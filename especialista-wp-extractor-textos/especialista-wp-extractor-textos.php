<?php
/**
 * @package EspecialistaWPExtractorTextos
 * @version 1.0
 */
/*
Plugin Name: Especialista WP Extractor de textos
Plugin URI: https://especialistawp.com/especialistas-wordpress-extractor-textos/
Description: Este plugin permite extraer todos los textos de tu wordpress (Base de datos y archivos PO) y generar un HTML para entregar al traductor. 
Version: 1.0
Author: especialistawp.com
Author URI: https://especialistawp.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action( 'admin_menu', 'especialista_wp_extractor_textos_plugin_menu' );
function especialista_wp_extractor_textos_plugin_menu() {
	add_options_page( __('Extraer textos', 'especialista_wp_extractor'), __('Extraer textos', 'especialista_wp_extractor'), 'manage_options', 'especialista_wp_extractor', 'especialista_wp_extractor_textos_page_settings' );
}

function especialista_wp_extractor_textos_page_settings() { 
	global $wp_registered_sidebars, $l10n, $l10n_unloaded;
	?><h1><?php _e("Extractor de textos para traducción", "especialista_wp_extractor"); ?></h1><?php
	$show_post_types = get_post_types();
	unset($show_post_types['attachment']);
	unset($show_post_types['revision']);
	unset($show_post_types['nav_menu_item']);
	unset($show_post_types['custom_css']);
	unset($show_post_types['customize_changeset']);
	unset($show_post_types['oembed_cache']);

	$show_taxonomies = get_taxonomies();
	unset($show_taxonomies['nav_menu']);
	unset($show_taxonomies['link_category']);
	unset($show_taxonomies['post_format']);



	$meta = get_option('wpseo_taxonomy_meta');
	//echo "<pre>"; print_r (); echo "</pre>";
	if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') {
		//print_r ($_REQUEST);
		ob_start(); ?>
		<!doctype html>
		<html class="no-js" lang="es" dir="ltr">
		<head>
			<meta charset="utf-8">
			<style>
				img, iframe { display: none; }
				* { background-image: none !important; }
			</style>
		</head>
		<body>
			<table border="1" cellpadding="3">
				<?php foreach ($_REQUEST['custom_post_types'] as $custom_post_type) { ?>
					<tr>
						<th colspan="2" bgcolor="green" style="color: white;"><?php _e("Item", "especialista_wp_extractor"); ?>: <?php echo $custom_post_type; ?></th>
					</tr>
					<?php $args = array(
						'post_type' => $custom_post_type,
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'order' => 'ASC',
						'orderby' => 'menu_order',
					);
					$the_query = new WP_Query( $args); 
					while ( $the_query->have_posts() ) {  
						$the_query->the_post(); $post = get_post(get_the_ID()); ?>
						<tr>
							<th><?php _e("Título", "especialista_wp_extractor"); ?></th>
							<td><?php echo get_the_title($post->ID); ?></td>
						</tr>
						<tr>
							<th><?php _e("Slug", "especialista_wp_extractor"); ?></th>
							<td><?php echo $post->post_name; ?></td>
						</tr>
						<?php if(get_post_meta($post->ID, '_yoast_wpseo_title', true ) != '') { ?>
						<tr>
							<th><?php _e("Metatítulo", "especialista_wp_extractor"); ?></th>
							<td><?php echo get_post_meta($post->ID, '_yoast_wpseo_title', true ); ?></td>
						</tr>
						<?php } ?>
						<?php if(get_post_meta($post->ID, '_yoast_wpseo_metadesc', true ) != '') { ?>
						<tr>
							<th><?php _e("Metadescripción", "especialista_wp_extractor"); ?></th>
							<td><?php echo get_post_meta($post->ID, '_yoast_wpseo_metadesc', true ); ?></td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="2"><?php the_content(); ?></td>
						</tr>
						<tr>
							<td colspan="2"></td>
						</tr>
					<?php } ?>
				<?php } ?>
				<?php foreach ($_REQUEST['taxonomies'] as $taxonomy) { ?>
					<tr>
						<th colspan="2" bgcolor="green" style="color: white;"><?php _e("Taxonomía", "especialista_wp_extractor"); ?>: <?php echo $taxonomy; ?></th>
					</tr>
					<?php  foreach(get_terms($taxonomy) as $term) { $term_id = $term->term_id; ?>
						<tr>
							<th><?php _e("Título", "especialista_wp_extractor"); ?></th>
							<td><?php echo $term->name; ?></td>
						</tr>
						<tr>
							<th><?php _e("Slug", "especialista_wp_extractor"); ?></th>
							<td><?php echo $term->slug; ?></td>
						</tr>
						<?php if($meta[$taxonomy][$term_id]['wpseo_title'] != '') { ?><tr>
							<th><?php _e("Metatítulo", "especialista_wp_extractor"); ?></th>
							<td><?php echo $meta[$taxonomy][$term_id]['wpseo_title']; ?></td>
						</tr><?php } ?>
						<?php if($meta[$taxonomy][$term_id]['wpseo_desc'] != '') { ?><tr>
							<th><?php _e("Metadescripción", "especialista_wp_extractor"); ?></th>
							<td><?php echo $meta[$taxonomy][$term_id]['wpseo_desc']; ?></td>
						</tr><?php } ?>

						<?php if($term->description != '') { ?><tr>
							<td colspan="2"><?php echo $term->description; ?></td>
						</tr><?php } ?>
						<tr>
							<td colspan="2"></td>
						</tr>
					<?php } ?>
				<?php } ?>
				<?php if(isset($_REQUEST['menus']) && $_REQUEST['menus'] == '1') { ?>
					<tr>
						<th colspan="2" bgcolor="green" style="color: white;"><?php _e("Menús", "especialista_wp_extractor"); ?></th>
					</tr>
					<?php foreach(get_terms( 'nav_menu') as $menu) { ?>
						<tr>
							<td colspan="2"><?php wp_nav_menu(array('menu' => $menu->name, 'container' => false)); ?></td>
						</tr>
					<?php } ?>
				<?php } ?>
				<?php if(isset($_REQUEST['widgets']) && $_REQUEST['widgets'] == '1') { ?>
					<tr>
						<th colspan="2" bgcolor="green" style="color: white;"><?php _e("Widgets", "especialista_wp_extractor"); ?></th>
					</tr>
					<?php foreach($wp_registered_sidebars as $widget) { ?>		
					<tr>
						<td colspan="2"><?php dynamic_sidebar($widget['id']); ?></td>
					</tr>
					<?php } ?>
				<?php } ?>
				<?php if(isset($_REQUEST['pofile']) && $_REQUEST['pofile'] == '1') { ?>
					<tr>
						<th colspan="2" bgcolor="green" style="color: white;"><?php _e("Archivos PO", "especialista_wp_extractor"); ?></th>
					</tr>
					<?php 
						//
						foreach ($l10n as $domain) {
							foreach ((array) $domain as $value) {
								if(!is_array($value) && strstr($value, get_template_directory())) {
									$pofile = str_replace(".mo", ".po", $value); 
									break;
								}
							}
						} 
						preg_match_all('/msgid\s+\"([^\"]*)\"/', file_get_contents($pofile), $matches);
						foreach ($matches[1] as $msgid) { ?><?php if ($msgid != '')  { ?><tr><td colspan="2"><?php echo $msgid; ?></td></tr><?php } }
					?>
				<?php } ?>
				<?php if(isset($_REQUEST['others']) && $_REQUEST['others'] == '1') { ?>
					<tr>
						<th colspan="2" bgcolor="green" style="color: white;"><?php _e("Otros", "especialista_wp_extractor"); ?></th>
					</tr>
					<tr>
						<td colspan="2"><?php echo get_bloginfo('name'); ?></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo get_bloginfo('description'); ?></td>
					</tr>
				<?php } ?>
			</table>
		</body>
		</html>
		<?php $html = ob_get_clean(); //echo $html;
		$temp = wp_upload_dir();
		$f = fopen($temp['basedir']."/especialista_wp_extractor.html", "w+");
		fwrite($f, $html);
		fclose($f); 

		$zip = new ZipArchive();
		$zip->open($temp['basedir']."/especialista_wp_extractor.zip", ZipArchive::CREATE);
		$zip->addFile($temp['basedir']."/especialista_wp_extractor.html", 'especialista_wp_extractor.html');
		$zip->close();
		unlink($temp['basedir']."/especialista_wp_extractor.html");
		?>
		<p><?php _e("Descarga el fichero zip. Dentro está un html que puedes abrir con vualquier navegador y luego copiar el contenido en procesador de texto que uses.", "especialista_wp_extractor"); ?></p>
		<a href='<?php echo $temp['baseurl']."/especialista_wp_extractor.zip"; ?>' target="_blank" class="button button-primary"><?php _e("Descargar textos", "especialista_wp_extractor"); ?></a>
	<?php } else { ?>
		<form method="post">
			<h2><?php _e("Incluir", "especialista_wp_extractor"); ?>:</h2>
			<h3><?php _e("Tipos de posts", "especialista_wp_extractor"); ?>:</h3>
			<?php foreach ($show_post_types as $custom_post_type) { ?>
				<input type="checkbox" name="custom_post_types[]" value="<?php echo $custom_post_type; ?>" /> <?php echo $custom_post_type; ?></br></br>
			<?php } ?>
			<h3><?php _e("Taxonomías", "especialista_wp_extractor"); ?>:</h3>
			<?php foreach ($show_taxonomies as $taxonomy) { ?>
				<input type="checkbox" name="taxonomies[]" value="<?php echo $taxonomy; ?>" /> <?php echo $taxonomy; ?></br></br>
			<?php } ?>
			<h3><?php _e("Otros elementos", "especialista_wp_extractor"); ?>:</h3>
			<input type="checkbox" name="menus" value="1" /> <?php _e("Menús", "especialista_wp_extractor"); ?></br></br>
			<input type="checkbox" name="widgets" value="1" /> <?php _e("Widgets", "especialista_wp_extractor"); ?></br></br>
			<input type="checkbox" name="pofile" value="1" /> <?php _e("Archivos PO", "especialista_wp_extractor"); ?></br></br>
			<input type="checkbox" name="others" value="1" /> <?php _e("Otros", "especialista_wp_extractor"); ?></br></br>
			<input type="submit" name="send" class="button button-primary" value="<?php _e("Crear documento", "especialista_wp_extractor"); ?>" />
		</form>
	<?php }
} ?>
