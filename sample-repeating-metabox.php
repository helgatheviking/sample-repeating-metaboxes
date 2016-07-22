<?php
/**
 * Plugin Name: Sample Repeating Metaboxes
 * Plugin URI: http://www.kathyisawesome.com/
 * Description: Repeat some fields
 * Author: helgatheviking
 * Version: 1.0
 * Author URI: http://www.kathyisawesome.com/
 *
 */

/* 
 * Register our metabox
 */  
add_action( 'add_meta_boxes', 'add_meta_boxes' );

function add_meta_boxes() {
    add_meta_box( 'repeatable_fields', __( 'Sample Repeating Field Groups', 'sample-repeating-metabox' ), 'repeatable_meta_box_display', 'post', 'normal', 'default' );
}

/* 
 * Register our metabox
 */  
add_action( 'admin_enqueue_scripts', 'repeatable_meta_box_scripts', 99 );

function repeatable_meta_box_scripts() {
    wp_register_script( 'sample-repeating-metabox', plugins_url( 'js/sample-repeating-metabox.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable', 'backbone', 'word-count', 'editor', 'quicktags', 'wplink', 'media-upload' ), '1.0.0', true );

}

/* 
 * Our metabox display callback
 * @param obj $post
 */ 
function repeatable_meta_box_display( $post ) { 

    $post_id = $post->ID;

    wp_nonce_field( 'repeatable_meta_box_nonce', '_repeatable_meta_box_nonce' ); 

    $meta = get_post_meta( $post->ID, '_repeatable_fields', true );

    $user_states = json_decode( get_user_meta( get_current_user_id(), '_sections_states', true ) );

    $toggleStates = ! empty( $user_states ) && isset( $user_states->$post_id ) ? $user_states->$post_id : array();

    // merge existing states into meta array
    if( is_array( $meta ) ){
        foreach( $meta as $i => $m ){
            if( isset( $toggleStates[$i] ) ){
                $meta[$i]['state'] = $toggleStates[$i];
            }
        }
    }

    $l10n = array( 'confirm' => __( 'This action can not be undone, are you sure?', 'sample-repeating-metabox' ),
                   'ajaxurl' => admin_url('admin-ajax.php'),
                   'nonce' => wp_create_nonce( 'repeatable_meta_box_nonce' ),
                    'meta' => $meta,
                    'cloneButton' => '#repeater-button',
                    'clearButton' => '#clear-button',
                    'sectionContainer' => '#sectionContainer',
                    'sectionTempl' => '#sectionTemplate',
                    'post_id' => $post->ID ,

            );

    wp_enqueue_script( 'sample-repeating-metabox' );
    
    wp_localize_script( 'sample-repeating-metabox', 'Sample_Repeating_Metabox', $l10n );

    include_once( 'templates/metabox.templ.php' );

}


/* 
 * Save the metabox
 */  
add_action('save_post', 'repeatable_meta_box_save' ); 

function repeatable_meta_box_save( $post_id ) { 

    if ( ! isset( $_POST['_repeatable_meta_box_nonce'] ) || 
    ! wp_verify_nonce( $_POST['_repeatable_meta_box_nonce'], 'repeatable_meta_box_nonce' ) ){ error_log('nonce failure');
        return;
    } 
        
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
        return;
    } 

    if ( ! current_user_can('edit_post', $post_id ) ){ error_log('user permission error');
        return;
    } 

    $clean = array(); 

    $allowed = array ( 'alpha', 'beta', 'gamma' );

    if  ( isset ( $_POST['sections'] ) && is_array( $_POST['sections'] ) ) :

        foreach ( $_POST['sections'] as $i => $section ){

            $clean[] = array( 
                'image' => isset( $section['image'] ) ? sanitize_text_field( $section['image'] ) : null,
                'input' => isset( $section['input'] ) ? sanitize_text_field( $section['input'] ) : null,
                'select' => isset( $section['select'] ) && in_array( $section['select'], $allowed ) ? $section['select'] : null,
                'textarea' => isset( $section['textarea'] ) ? sanitize_post_field( 'post_content', $section['textarea'], $post_id, 'db' ) : null,
                );

        }

    endif;

    // save data 
    if ( ! empty( $clean ) ) { 
        update_post_meta( $post_id, '_repeatable_fields', $clean ); 
    } else {
        delete_post_meta( $post_id, '_repeatable_fields' ); 
    }

}


// Accept an Ajax Request
add_action( 'wp_ajax_save_section_state', 'repeatable_meta_box_save_state' );
function repeatable_meta_box_save_state(){

    // @todo: verify nonce

    $sections_states = get_user_meta( get_current_user_id(), '_sections_states', true );

    $sections_states = $sections_states == '' ? new stdClass() : json_decode( $sections_states );

    if( isset( $_POST['states'] ) && isset( $_POST['post_id'] ) ){
        $post_id = intval( $_POST['post_id'] );
        
        $states = $_POST['states'];

        $sections_states->$post_id = $states;
        update_user_meta( get_current_user_id(), '_sections_states', json_encode($sections_states) );
    }

    die();
    
}