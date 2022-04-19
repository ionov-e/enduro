<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly
?>
<!--Mine-Mine Были какие-то изменения-->

<figure class="error_404">
    <img src="/wp-content/uploads/manual-up/404-cropped-halved.gif" alt="404 gif">
    <figcaption>Что-то пошло не так</figcaption>
</figure>


<p style="padding-left: 20px;">Воспользуйся поиском или перейди на нашу <a href="/" style="font-weight: bold;">главную страницу</a></p>



<section class="recommendation_404_custom">
    <p><strong>Для улучшения эффективности поиска:</strong></p>
    <ul class="borderlist-not">
        <li>Всегда проверяй правописание.</li>
        <li>Используй синонимы. Например: мотоцикл вместо байка.</li>
        <li>Попробуй использовать более одного слова для поиска.</li>
    </ul>
</section>

<p class='entry-content error_404_top'><strong><?php _e('Nothing Found', 'avia_framework'); ?></strong><br/>

<?php _e('Sorry, the post you are looking for is not available. Maybe you want to perform a search?', 'avia_framework'); ?>
</p>
<?php

		if(isset($_GET['post_type']) && $_GET['post_type'] == 'product' && function_exists('get_product_search_form'))
		{
			get_product_search_form();
		}
		else
		{
			get_search_form();
		}

?>



<div class='hr_invisible'></div>

<section class="recommendation_404">
    <p><strong><?php _e('For best search results, mind the following suggestions:', 'avia_framework'); ?></strong></p>
    <ul class='borderlist-not'>
        <li><?php _e('Always double check your spelling.', 'avia_framework'); ?></li>
        <li><?php _e('Try similar keywords, for example: tablet instead of laptop.', 'avia_framework'); ?></li>
        <li><?php _e('Try using more than one keyword.', 'avia_framework'); ?></li>
    </ul>

    <div class='hr_invisible'></div>
    <?php
    do_action('ava_after_content', '', 'error404');
    ?>
</section>
