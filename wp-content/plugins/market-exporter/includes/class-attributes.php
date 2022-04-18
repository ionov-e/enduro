<?php
/**
 * Attributes class.
 *
 * @link       https://wooya.ru
 * @since      2.0.6
 *
 * @package Wooya\Includes
 */

namespace Wooya\Includes;

use WC_Product;
use WC_Product_Attribute;
use WC_Product_Variation;

/**
 * Class Generator
 */
class Attributes {

	/**
	 * Settings variable
	 *
	 * @access private
	 * @var mixed|void
	 */
	protected $settings;

	/**
	 * Array of categories.
	 *
	 * @since 2.0.6
	 * @var array List of categories.
	 */
	protected $categories = array();

	/**
	 * Spaces before <offer> element.
	 */
	const ME_OFFER_SPACING = 6;

	/**
	 * Spaces before any element inside <offer>.
	 * <offer>
	 *     <spacing for this attribute />
	 * </offer>
	 */
	const ME_OFFER_ATTRIBUTE_SPACING = 8;

	/**
	 * Spacing for any internal attribute inside an attribute in <offer>
	 * <offer>
	 *     <attribute>
	 *         <spacing for this attribute />
	 *     </attribute>
	 * </offer>
	 */
	const ME_OFFER_ATTRIBUTE_SPACING_ADDITIONAL = 10;

	/**
	 * Attributes constructor.
	 */
	public function __construct() {
		$this->settings = get_option( 'wooya_settings' );
	}

	/**
	 * Generate the first <offer> line for each product.
	 *
	 * @param int  $offer_id           Offer ID.
	 * @param bool $vendor_model_type  Is complex product.
	 *
	 * @return string
	 */
	protected function get_offer( $offer_id, $vendor_model_type ) {

		/* @global WC_Product|WC_Product_Variation $offer  Offer. */
		global $offer;

		$stock_status = $offer->get_stock_status();

		$available = 'instock' === $stock_status ? 'true' : 'false';

		if ( isset( $this->settings['offer']['backorders'] ) && $this->settings['offer']['backorders'] && 'onbackorder' === $stock_status ) {
			$available = 'true';
		}

		$offer_element = $this->add_child( 'offer', null, self::ME_OFFER_SPACING );
		$offer_element = $this->add_attribute( $offer_element, 'id', $offer_id );

		if ( $vendor_model_type ) {
			$offer_element = $this->add_attribute( $offer_element, 'type', 'vendor.model' );
		}

		$offer_element = $this->add_attribute( $offer_element, 'available', $available );
		return $offer_element;

	}

	/**
	 * Generate <price> and/or <oldprice> attributes.
	 *
	 * @return string
	 */
	protected function get_price() {

		/* @global WC_Product|WC_Product_Variation $offer  Offer. */
		global $offer;

		if ( $offer->get_sale_price() && ( $offer->get_sale_price() < $offer->get_regular_price() ) ) {
			$yml  = $this->add_child( 'price', $offer->get_sale_price() );
			$yml .= $this->add_child( 'oldprice', $offer->get_regular_price() );
		} else {
			$yml = $this->add_child( 'price', apply_filters( 'me_product_price', $offer->get_regular_price(), $offer->get_id() ) );
		}

		return $yml;

	}

	/**
	 * Get category ID for offer.
	 *
	 * Not using $offer_id, because variable products inherit category from parent.
	 *
	 * @return string|void
	 */
	protected function get_category_id() {

		/* @global WC_Product $product  Product instance. */
		global $product;

		$categories = get_the_terms( $product->get_id(), 'product_cat' );

		if ( ! $categories ) {
			// TODO: Show some warning if no category is selected.
			return;
		}

		foreach ( $categories as $category ) {
			if ( array_key_exists( $category->term_id, $this->categories ) ) {
				return $this->add_child( 'categoryId', $category->term_id );
			}
		}

		/**
		 * Temp compatibility code.
		 *
		 * The above foreach loop should find a matching category... If it doesn't - at least add the first
		 * category in the list.
		 *
		 * @since 2.0.7
		 */
		$category = array_shift( $categories );
		return $this->add_child( 'categoryId', $category->term_id );

	}

	/**
	 * Generate deliver-options attributes.
	 *
	 * This is kind of a complicated part. But we're just removing the duplicate code - if we have custom defines per
	 * product, we use them. If not - return.
	 *
	 * @param bool $global   Global settings or not.
	 *
	 * @return bool|string  Return false if no deliver_options set.
	 */
	protected function get_delivery_options( $global = true ) {

		/* @global WC_Product $product  Product instance. */
		global $product;

		if ( ! isset( $this->settings['delivery']['delivery_options'] ) || ! $this->settings['delivery']['delivery_options'] ) {
			return false;
		}

		if ( ! $global ) {
			// Per post settings.
			$cost         = get_post_custom_values( 'me_do_cost', $product->get_id() );
			$days         = apply_filters( 'me_custom_do_days', get_post_custom_values( 'me_do_days', $product->get_id() ), $product->get_id() );
			$order_before = get_post_custom_values( 'me_do_order_before', $product->get_id() );

			// If not global options, and not defined per product - no need to continue.
			if ( ! isset( $cost ) && ! isset( $days ) && ! isset( $order_before ) ) {
				return false;
			}
		}

		$attribute_spacing     = $global ? self::ME_OFFER_ATTRIBUTE_SPACING - 4 : self::ME_OFFER_ATTRIBUTE_SPACING;
		$sub_attribute_spacing = $global ? self::ME_OFFER_ATTRIBUTE_SPACING_ADDITIONAL - 4 : self::ME_OFFER_ATTRIBUTE_SPACING_ADDITIONAL;

		// Settings for delivery-options.
		$yml = $this->add_child( 'delivery-options', null, $attribute_spacing );

		$option = $this->add_child( 'option', null, $sub_attribute_spacing, false, false );

		$cost = ! $global && isset( $cost ) ? $cost[0] : $this->settings['delivery']['cost'];
		$days = ! $global && isset( $days ) ? $days[0] : $this->settings['delivery']['days'];

		$option = $this->add_attribute( $option, 'cost', $cost );
		$option = $this->add_attribute( $option, 'days', $days );
		if ( isset( $this->settings['delivery']['order_before'] ) && ! empty( $this->settings['delivery']['order_before'] ) ) {
			$order_before = ! $global && isset( $order_before ) ? $order_before[0] : $this->settings['delivery']['order_before'];
			$option       = $this->add_attribute( $option, 'order-before', $order_before );
		}
		$yml .= $option;
		$yml .= $this->add_child( 'delivery-options', null, $attribute_spacing, true );

		return $yml;

	}

	/**
	 * Get product images.
	 *
	 * @return string
	 */
	protected function get_images() {

		/**
		 * Global variables.
		 *
		 * @global WC_Product $product                     Product.
		 * @global WC_Product|WC_Product_Variation $offer  Offer.
		 */
		global $product, $offer;

		$yml = '';

		// Get images.
		$main_image = get_the_post_thumbnail_url( $offer->get_id(), 'medium' );  //  MINE-MINE (medium instead of full)

		// If no image found for product, it's probably a variation without an image, get the image from parent.
		if ( ! $main_image ) {
			$main_image = get_the_post_thumbnail_url( $product->get_id(), 'medium' );  //  MINE-MINE (medium instead of full)
		}
		
		$main_image = str_replace( '.jpg', '_yandex_.jpg', $main_image );  //  MINE-MINE
		$main_image = str_replace( '.jpeg', '_yandex_.jpeg', $main_image );  //  MINE-MINE
		$main_image = str_replace( '.png', '_yandex_.png', $main_image );  //  MINE-MINE
		//$main_image = str_replace( '-scaled', '-scaled_yandex_', $main_image );  //  MINE-MINE Author's suggestion

		if ( false !== $main_image && strlen( utf8_decode( $main_image ) ) <= 512 ) {
			$yml .= $this->add_child( 'picture', esc_url( $main_image ) );
		}

		if ( Helper::woo_latest_versions() ) {
			$attachment_ids = $product->get_gallery_image_ids();
		} else {
			$attachment_ids = $product->get_gallery_attachment_ids();
		}

		// Each product can have max 10 images, one was added on top.
		if ( count( $attachment_ids ) > 9 ) {
			$attachment_ids = array_slice( $attachment_ids, 0, 9 );
		}

		// Check if there's a setting for image count. If not - get the default.
		if ( isset( $this->settings['offer']['image_count'] ) ) {
			$image_count = $this->settings['offer']['image_count'];
		} else {
			$defaults    = Elements::get_elements();
			$image_count = $defaults['offer']['image_count']['default'];
		}

		if ( 1 < $image_count ) {
			$exported = 1;
			while ( $exported < $image_count ) {
				if ( ! isset( $attachment_ids[ $exported - 1 ] ) ) {
					break;
				}

                $image = wp_get_attachment_image_url( $attachment_ids[ $exported - 1 ] , 'medium');  //  MINE-MINE different function. with size
                $image = str_replace( '.jpg', '_yandex_.jpg', $image );  //  MINE-MINE
                $image = str_replace( '.jpeg', '_yandex_.jpeg', $image );  //  MINE-MINE
                $image = str_replace( '.png', '_yandex_.png', $image );  //  MINE-MINE
                if ( false !== $image && strlen( utf8_decode( $image ) ) <= 512 && $image !== $main_image ) {
                    $yml .= $this->add_child( 'picture', esc_url( $image ) );
                }

                /**
                 * MINE-MINE comment: 4 lines (no additional pictures

				$image = wp_get_attachment_url( $attachment_ids[ $exported - 1 ] );
				if ( false !== $image && strlen( utf8_decode( $image ) ) <= 512 && $image !== $main_image ) {
					$yml .= $this->add_child( 'picture', esc_url( $image ) );
				}
                 */
				$exported ++;
			}
		}

		return $yml;

	}

	/**
	 * Product dimensions. Params: size and weight.
	 *
	 * @return bool|string
	 */
	protected function get_dimensions() {

		/* @global WC_Product|WC_Product_Variation $offer  Offer. */
		global $offer;

		if ( ! isset( $this->settings['offer']['size'] ) || ! $this->settings['offer']['size'] ) {
			return false;
		}

		$yml = '';

		$weight_unit = esc_attr( get_option( 'woocommerce_weight_unit' ) );
		$size_unit   = esc_attr( get_option( 'woocommerce_dimension_unit' ) );

		if ( $offer->has_weight() && 'kg' === $weight_unit ) {
			$yml .= $this->add_child( 'weight', $offer->get_weight() );
		}

		if ( $offer->has_dimensions() ) {
			if ( Helper::woo_latest_versions() ) {
				// WooCommerce version 3.0 and higher.
				$dimensions = $offer->get_dimensions( false );
			} else {
				// WooCommerce 2.6 and lower.
				$dimensions = [
					'length' => $offer->get_length(),
					'width'  => $offer->get_width(),
					'height' => $offer->get_height(),
				];
			}

			switch ( $size_unit ) {
				case 'm':
					$dimensions = [
						'length' => $dimensions['length'] * 100,
						'width'  => $dimensions['width'] * 100,
						'height' => $dimensions['height'] * 100,
					];
					break;
				case 'mm':
					$dimensions = [
						'length' => $dimensions['length'] / 10,
						'width'  => $dimensions['width'] / 10,
						'height' => $dimensions['height'] / 10,
					];
					break;
				case 'in':
					$dimensions = [
						'length' => $dimensions['length'] * 2.54,
						'width'  => $dimensions['width'] * 2.54,
						'height' => $dimensions['height'] * 2.54,
					];
					break;
				case 'yd':
					$dimensions = [
						'length' => $dimensions['length'] * 91.44,
						'width'  => $dimensions['width'] * 91.44,
						'height' => $dimensions['height'] * 91.44,
					];
					break;
				case 'cm':
				case 'default':
					// Nothing to do.
					break;
			}

			$dimensions = implode( '/', $dimensions );

			$yml .= $this->add_child( 'dimensions', $dimensions );
		}

		return $yml;

	}

	/**
	 * Params: stock_quantity.
	 *
	 * @return bool|string
	 */
	protected function get_stock_quantity() {

		/* @global WC_Product $product  Product instance. */
		global $offer;

		if ( ! isset( $this->settings['offer']['stock_quantity'] ) || ! $this->settings['offer']['stock_quantity'] ) {
			return false;
		}

		// Compatibility for WC versions from 2.5.x to 3.0+.
		if ( method_exists( $offer, 'get_manage_stock' ) ) {
			$stock_status = $offer->get_manage_stock(); // For version 3.0+.
		} else {
			$stock_status = $offer->manage_stock; // Older than version 3.0.
		}

		if ( $stock_status ) {
			// Compatibility for WC versions from 2.5.x to 3.0+.
			if ( method_exists( $offer, 'get_stock_quantity' ) ) {
				$stock_quqntity = $offer->get_stock_quantity(); // For version 3.0+.
			} else {
				$stock_quqntity = $offer->stock_quqntity; // Older than version 3.0.
			}

			if ( isset( $stock_quqntity ) && 0 < $stock_quqntity ) {
				return $this->add_child( 'stock_quantity', absint( $stock_quqntity ) );
			}
		}

		return false;

	}

	/**
	 * Get params.
	 *
	 * @return string
	 */
	protected function get_params() {

		/**
		 * Global variables.
		 *
		 * @global WC_Product $product                     Product.
		 * @global WC_Product|WC_Product_Variation $offer  Offer.
		 */
		global $product, $offer;

		$yml = '';

		// Params: selected parameters.
		if ( isset( $this->settings['offer']['params'] ) && ! empty( $this->settings['offer']['params'] ) ) {
			$attributes = $product->get_attributes();
			foreach ( $this->settings['offer']['params'] as $param ) {
				// Encode the name, because cyrillic letters won't work in array_key_exists.
				$encoded = strtolower( rawurlencode( $param['value'] ) );
				if ( ! array_key_exists( 'pa_' . $encoded, $attributes ) ) {
					continue;
				}

				// See https://wordpress.org/support/topic/атрибуты-вариантивного-товара/#post-9607195.
				$param_value = $offer->get_attribute( $param['value'] );
				if ( empty( $param_value ) ) {
					$param_value = $product->get_attribute( $param['value'] );
				}

				$yml .= $this->generate_param( $param['value'], $param_value );
			}

			return $yml;
		}

		/**
		 * Parameter.
		 *
		 * @var WC_Product_Attribute|array $param
		 */
		foreach ( $product->get_attributes() as $param ) {
			if ( Helper::woo_latest_versions() ) {
				$taxonomy = wc_attribute_taxonomy_name_by_id( $param->get_id() );
			} else {
				$taxonomy = $param['name'];
			}

			if ( isset( $param['variation'] ) && true === $param['variation'] || isset( $param['is_variation'] ) && 1 === $param['is_variation'] ) {
				$param_value = $offer->get_attribute( $taxonomy );
			} else {
				$param_value = $product->get_attribute( $taxonomy );
			}

			$yml .= $this->generate_param( $taxonomy, $param_value );
		}

		return $yml;

	}


	/**
	 * Generate <param> attribute.
	 *
	 * @since 2.0.0
	 *
	 * @param string $taxomnomy  Taxonomy label.
	 * @param string $value      Taxonomy value.
	 *
	 * @return bool|string
	 */
	private function generate_param( $taxomnomy, $value ) {

		// Skip if empty value (when cyrillic letter are used in attribute slug).
		if ( ! isset( $value ) || empty( $value ) ) {
			return false;
		}

		$yml    = '';
		$params = explode( ',', $value );
		foreach ( $params as $single_param ) {
			$param_name  = apply_filters( 'me_param_name', wc_attribute_label( $taxomnomy ), $this->attribute_taxonomy_slug( $taxomnomy ) );
			$param_value = apply_filters( 'me_param_value', trim( $single_param ) );
			$param_unit  = apply_filters( 'me_param_unit', false, $param_name );

			$yml .= '        <param name="' . $param_name . '" ' . $param_unit . '>' . $param_value . '</param>' . PHP_EOL;
		}

		return $yml;

	}

	/**
	 * Get product description.
	 *
	 * @since   1.0.0
	 * @used-by ME_WC::yml_offers()
	 * @param   string $type  Description type. Accepts: default, long, short.
	 * @return  string
	 */
	protected function get_description( $type = 'default' ) {

		/**
		 * Global variables.
		 *
		 * @global WC_Product $product                     Product.
		 * @global WC_Product|WC_Product_Variation $offer  Offer.
		 */
		global $product, $offer;

		$description = '';

		switch ( $type ) {
			case 'default':
				if ( Helper::woo_latest_versions() ) {
					// Try to get variation description.
					$description = $offer->get_description();
					// If not there - get product description.
					if ( empty( $description ) ) {
						$description = $product->get_description();
					}
				} else {
					if ( $product->is_type( 'variable' ) && ! $offer->get_variation_description() ) {
						$description = $offer->get_variation_description();
					} else {
						$description = $offer->post->post_content;
					}
				}
				break;
			case 'long':
				// Get product description.
				if ( Helper::woo_latest_versions() ) {
					$description = $product->get_description();
				} else {
					$description = $offer->post->post_content;
				}
				break;
			case 'short':
				// Get product short description.
				if ( Helper::woo_latest_versions() ) {
					$description = $product->get_short_description();
				} else {
					$description = $offer->post->post_excerpt;
				}
				break;
		}

		// Leave in only allowed html tags.
		$description = strip_tags( strip_shortcodes( $description ), '<h3><ul><li><p>' );
		$description = html_entity_decode( $description, ENT_COMPAT, 'UTF-8' );


        //MINE-MINE Start //description-compatibility add-on
        $product_attributes = $product->get_attributes();

        if(!empty($product_attributes)){
            $new_line1 = '
            Совместимость с эндуро мотоциклами: ';
            $new_line2 =  $product->get_attribute( 'pa_compatibility-real' );
            $description .= $new_line1 . $new_line2;
        }
        //MINE-MINE End


		// Cannot be longer then 3000 characters.
		// This causes an error on many installs
		// $description = substr( $description, 0, 2999 );.
		return $description;

	}

	/**
	 * Generate attribute string.
	 *
	 * This one needs a bit of explanation. If there's no value present, it's an open attribute and, in theory
	 * should always end with />, but because it might be an internal part of some attribute (see delivery-options or
	 * categories for example), we need to know when to add />. For this we use $has_children attribute. If no children
	 * and no value is present, we close the attribute with /..
	 *
	 * @since 2.0.6
	 *
	 * @param string $name          Attribute name.
	 * @param string $value         Attribute vale.
	 * @param int    $spacing       Spaces before the attribute. Minimum of 4 spaces.
	 * @param bool   $closing       Is a closing tag.
	 * @param bool   $has_children  Has internal objects.
	 *
	 * @return string
	 */
	protected function add_child( $name, $value, $spacing = 8, $closing = false, $has_children = true ) {

		if ( ! $value && ! is_null( $value ) && ! $closing ) {
			return '';
		}

		if ( $value ) {
			return str_repeat( ' ', $spacing ) . "<{$name}>{$value}</{$name}>" . PHP_EOL;
		} elseif ( ! $has_children ) {
			$name = "{$name} /";
		}

		if ( $closing ) {
			$name = "/{$name}";
		}

		return str_repeat( ' ', $spacing ) . "<{$name}>" . PHP_EOL;

	}

	/**
	 * Add attribute to a child element.
	 *
	 * @since 2.0.6
	 *
	 * @param string $child  Child element.
	 * @param string $name   Attribute name.
	 * @param string $value  Attribute value.
	 *
	 * @return string
	 */
	protected function add_attribute( $child, $name, $value ) {

		$closing = false === strpos( $child, '/>' ) ? '>' : ' />';
		return str_replace( $closing, " {$name}=\"{$value}\"{$closing}", $child );

	}

	/**
	 * Get an unprefixed product attribute name.
	 *
	 * Compatibility method for support of WooCommerce 3.6 and older.
	 *
	 * @since 2.1.0
	 *
	 * @param string $taxomnomy  Attribute name.
	 *
	 * @return string
	 */
	private function attribute_taxonomy_slug( $taxomnomy ) {

		if ( Helper::woo_latest_versions( '3.5.8' ) ) {
			return wc_attribute_taxonomy_slug( $taxomnomy );
		}

		$attribute_name = wc_sanitize_taxonomy_name( $taxomnomy );
		return 0 === strpos( $attribute_name, 'pa_' ) ? substr( $attribute_name, 3 ) : $attribute_name;

	}

}
