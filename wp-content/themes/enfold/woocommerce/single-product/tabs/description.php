<?php
/**
 * Description tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;
global $product;//Mine-Mine line
global $product_attributes;//Mine-Mine line

$heading = apply_filters( 'woocommerce_product_description_heading', __( 'Description', 'woocommerce' ) );

?>

<?php if ( $heading ) : ?>
	<h2><?php echo esc_html( $heading ); ?></h2>
<?php endif; ?>

<?php the_content(); ?>

<!-- Mine-Mine Beginning -->
<?php 
//echo wc_get_product_tag_list( $product->get_id(), '<br/>', '<span class="custom_tags_descr">' . _n( 'Совместимость с эндуро мотоциклом:<br/>', 'Совместимость с эндуро мотоциклами:<br/>', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ',
//'</span>' ); 

/*//Mine-Mine Absolutely working thing!
        //Getting product attributes
        $product_attributes = $product->get_attributes();

        if(!empty($product_attributes)){

            //Getting product attributes slugs
            $product_attribute_slugs = array_keys($product_attributes);
            $count_slug = 0;

            echo '<span class="compatibility-real">Совместимость с эндуро мотоциклами: ';

            foreach ($product_attribute_slugs as $product_attribute_slug){
				if($product_attribute_slug != "pa_compatibility-real") continue;
				else					
                $count_slug++;
                // Removing "pa_" from attribute slug and adding a cap to first letter
                // $attribute_name =  ucfirst( str_replace('pa_', '', $product_attribute_slug) );
                // echo $attribute_name . ' (';

##  ===>  ===>  // Getting the product attribute values
                $attribute_values = get_terms($product_attribute_slug);
                $count_value = 0;
                foreach($attribute_values as $attribute_value){
                    $count_value++;
                    $attribute_name_value = $attribute_value->name; // name value
                    $attribute_slug_value = $attribute_value->slug; // slug value
                    $attribute_slug_value = $attribute_value->term_id; // ID value

                    // Displaying HERE the "names" values for an attribute
                    echo $attribute_name_value;
                    if($count_value != count($attribute_values)) echo ', ';
                }
                //if($count_slug != count($product_attribute_slugs)) echo '), ';
                //else echo ').';

			}
            echo '</span>';
        }


*/

//echo wc_get_product_tag_list( $product->get_id(), '<br/>', '<div class="custom_tags_descr">' . _n( '', '', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ',
//'</div>' );





/** original copy-paste

        //Getting product attributes
        $product_attributes = $product->get_attributes();

        if(!empty($product_attributes)){

            //Getting product attributes slugs
            $product_attribute_slugs = array_keys($product_attributes);
            $count_slug = 0;

            echo '<div class="product_attributes">';

            foreach ($product_attribute_slugs as $product_attribute_slug){
                $count_slug++;

                // Removing "pa_" from attribute slug and adding a cap to first letter
                $attribute_name =  ucfirst( str_replace('pa_', '', $product_attribute_slug) );
                echo $attribute_name . ' (';

##  ===>  ===>  // Getting the product attribute values
                $attribute_values = get_terms($product_attribute_slug);
                $count_value = 0;
                foreach($attribute_values as $attribute_value){
                    $count_value++;
                    $attribute_name_value = $attribute_value->name; // name value
                    $attribute_slug_value = $attribute_value->slug; // slug value
                    $attribute_slug_value = $attribute_value->term_id; // ID value

                    // Displaying HERE the "names" values for an attribute
                    echo $attribute_name_value;
                    if($count_value != count($attribute_values)) echo ', ';
                }
                if($count_slug != count($product_attribute_slugs)) echo '), ';
                else echo ').';
            }
            echo '</div>';
        }
*/





/** golden copy-paste
	$attribute_names = array( 'pa_bikes' ); // Add attribute names here and remember to add the pa_ prefix to the attribute name
		
	foreach ( $attribute_names as $attribute_name ) {
		$taxonomy = get_taxonomy( $attribute_name );
		
		if ( $taxonomy && ! is_wp_error( $taxonomy ) ) {
			$terms = wp_get_post_terms( $post->ID, $attribute_name );
			$terms_array = array();
		
	        if ( ! empty( $terms ) ) {
		        foreach ( $terms as $term ) {
			       $archive_link = get_term_link( $term->slug, $attribute_name );
			       $full_line = '<a href="' . $archive_link . '">'. $term->name . '</a>';
			       array_push( $terms_array, $full_line );
		        }
		        echo $taxonomy->labels->name . ' ' . implode( $terms_array, ', ' );
	        }
    	}
    }
*/


        //Getting product attributes
        $product_attributes = $product->get_attributes();

        if(!empty($product_attributes)){
            echo '<span class="compatibility-real">Совместимость с эндуро мотоциклами: ';
			echo $product->get_attribute( 'pa_compatibility-real' );
            echo '</span>';	
        }

//echo wc_get_product_tag_list( $product->get_id(), '<br/>', '<div class="custom_tags_descr">' . _n( '', '', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', // WORKS!!! Tags, last my solution
//'</div>' );








	$attribute_names = array( 'pa_bikes' ); // Add attribute names here and remember to add the pa_ prefix to the attribute name
		
	foreach ( $attribute_names as $attribute_name ) {
		$taxonomy = get_taxonomy( $attribute_name );
		
		if ( $taxonomy && ! is_wp_error( $taxonomy ) ) {
			$terms = wp_get_post_terms( $post->ID, $attribute_name );
			$terms_array = array();
		
	        if ( ! empty( $terms ) ) {
		        foreach ( $terms as $term ) {
			       $archive_link = get_term_link( $term->slug, $attribute_name );
			       $full_line = '<a href="' . $archive_link . '">'. $term->name . '</a>';
			       array_push( $terms_array, $full_line );
		        }
		        echo '<div class="custom_tags_descr">' . implode( '<br/>', $terms_array );
				echo '</div>';
	        }
    	}
    }




?>

<a class="custom_credit_buy whatsapp_button_custom" href="https://wa.me/79951100072?text=Здравствуйте, меня интересует <?php echo esc_attr( $product->get_name() ); ?>" target="_blank" rel="noopener noreferrer"><i data-av_icon="" data-av_iconfont="entypo-fontello" title="WhatsApp"></i> Получить консультацию в WhatsApp</a>
<!-- Mine-Mine End -->




