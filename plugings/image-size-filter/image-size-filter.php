<?php
/*Plugin Name: Mostrar tamaño y dimensiones de las imágenes - Filtrable
Description: Este plugin muestra el tamaño y dimensiones de las imágenes en la librería de WordPress y permite ordenarlas.
Version: 1.2
Author: Nahuai Badiola
Author URI: https://nbadiola.com
License: GPLv2
 */

// Add filesize metadata to new images
add_action( 'added_post_meta', 'add_filesize_metadata_to_images', 10, 4 );
function add_filesize_metadata_to_images( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_wp_attachment_metadata' === $meta_key ) {
		$file = get_attached_file( $post_id );
		if ( file_exists( $file ) ) {
			update_post_meta( $post_id, 'filesize', filesize( $file ) );
		}
	}
}

// Also, let's ensure all images have filesize metadata
add_action('admin_init', 'ensure_all_images_have_filesize');
function ensure_all_images_have_filesize() {
    if (!is_admin()) return;
    
    // Only run this on the media library page
    global $pagenow;
    if ('upload.php' !== $pagenow) return;
    
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'filesize',
                'compare' => 'NOT EXISTS'
            )
        )
    );
    
    $attachments = get_posts($args);
    
    foreach ($attachments as $attachment) {
        $file = get_attached_file($attachment->ID);
        if (file_exists($file)) {
            update_post_meta($attachment->ID, 'filesize', filesize($file));
        }
    }
}

// Add the Dimensions column
add_filter( 'manage_upload_columns', 'cg_size_column_register' );
function cg_size_column_register( $columns ) {
	$columns['dimensiones'] = 'Dimensiones';
	return $columns;
}

// Add the Size column
function add_column_file_size( $posts_columns ) {
	$posts_columns['filesize'] = __( 'Tamaño' );
	return $posts_columns;
}

// Populate the Size column
add_filter( 'manage_media_columns', 'add_column_file_size' );
function add_column_value_file_size( $column_name, $post_id ) {
	if ( 'filesize' !== $column_name ) {
		return false;
	}

	// Always get fresh file size
	$file = get_attached_file( $post_id, true ); // true to force refresh
	if ( file_exists( $file ) ) {
		$file_size = filesize( $file );
		// Update the stored metadata
		update_post_meta( $post_id, 'filesize', $file_size );
		echo esc_html( size_format( $file_size, 0 ) );
	} else {
		echo 'File does not exist';
		return false;
	}
	return true;
}

// Make the column filterable
add_action( 'manage_media_custom_column', 'add_column_value_file_size', 10, 2 );
function add_column_sortable_file_size( $columns ) {
	$columns['filesize'] = 'filesize';
	return $columns;
}

// Column filtering logic
add_filter( 'manage_upload_sortable_columns', 'add_column_sortable_file_size' );
add_action( 'pre_get_posts', 'cg_sortable_file_size_sorting_logic' );
function cg_sortable_file_size_sorting_logic( $query ) {
    global $pagenow;
    if ( is_admin() && 'upload.php' == $pagenow && $query->is_main_query() ) {
        if ( isset($_REQUEST['orderby']) && 'filesize' == $_REQUEST['orderby'] ) {
            // Set post type explicitly
            $query->set('post_type', 'attachment');
            $query->set('post_status', 'inherit');
            
            // Simple meta key sorting without meta_query
            $query->set('meta_key', 'filesize');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', (isset($_REQUEST['order']) && 'desc' === $_REQUEST['order']) ? 'DESC' : 'ASC');
        }
    }
}

// Populate the Dimensions column
add_action( 'manage_media_custom_column', 'cg_size_column_display', 10, 2 );
function cg_size_column_display( $column_name, $post_id ) {
    if ( 'dimensiones' !== $column_name || ! wp_attachment_is_image( $post_id ) ) {
        return;
    }

    // First try WordPress metadata
    $metadata = wp_get_attachment_metadata( $post_id );
    if ( !empty( $metadata['width'] ) && !empty( $metadata['height'] ) ) {
        echo esc_html( "{$metadata['width']}&times;{$metadata['height']}px" );
        return;
    }

    // If WordPress metadata fails, try PHP's getimagesize
    $file_path = get_attached_file( $post_id );
    if ( file_exists( $file_path ) ) {
        $dimensions = getimagesize( $file_path );
        if ( $dimensions ) {
            echo esc_html( "{$dimensions[0]}&times;{$dimensions[1]}px" );
            
            // Update WordPress metadata for future use
            $metadata = array(
                'width' => $dimensions[0],
                'height' => $dimensions[1]
            );
            wp_update_attachment_metadata( $post_id, $metadata );
            return;
        }
    }

    echo 'N/A';
}

// Update filesize metadata when image is updated
add_action('wp_update_attachment_metadata', 'update_filesize_on_image_update', 10, 2);
add_action('add_attachment', 'update_filesize_on_image_update');
add_action('edit_attachment', 'update_filesize_on_image_update');

function update_filesize_on_image_update($post_id) {
	// Force clear any cached attachment metadata
	clean_attachment_cache($post_id);
	
	// Get fresh file path
	$file = get_attached_file($post_id, true); // true to force refresh the path
	
	if (file_exists($file)) {
		// Delete old metadata first
		delete_post_meta($post_id, 'filesize');
		// Add new metadata
		update_post_meta($post_id, 'filesize', filesize($file));
	}
}
