<style>
	.section { padding: 1em; border: solid 1px #ddd; background-color: #fdfdfd; margin-bottom: 1em;}
	.section h3 { margin: 0; }
	.section.open h3 { margin: 0; }
	.section.closed .section-inside{ display: none; }
	.section textarea { width: 99%; }
	.section label { display: block; }
	.section .section-controls { float: right; font-size: .8em; }
	.section input[type="text"] { display: block margin-bottom: 1.4em; }
	#repeater-button.dashicons-before::before { font-size: .75em; vertical-align: baseline; }
	.js .section .move-section { cursor: move; }
</style>

<div id="sectionContainer" class="widefat fixed sortable"> 

<?php wp_nonce_field( 'repeatable_meta_box_nonce', '_repeatable_meta_box_nonce' ); ?>

<!-- src/templates/metabox.templ.php  -->
 
<!-- Template -->
<script type="text/template" id="sectionTemplate">

<div class="section-wrap tmce-enabled" >
	
	<h3>
		<?php _e( 'Section','sample-repeating-metabox' );?>
		<div class="section-controls">
			<a class="toggle-section dashicons dashicons-arrow-down" href="#"  title="<?php _e( 'Click to toggle', 'generate-sections' );?>"></a>
			<a class="move-section dashicons dashicons-menu" href="#"  title="<?php _e( 'Click and drag to sort', 'generate-sections' );?>"></a>
			<a class="delete-section dashicons dashicons-no-alt" href="#" title="<?php esc_attr_e( 'Delete', 'sample-repeating-metabox' );?>"></a>
	   </div>   

	</h3>
		
	<div class="section-inside">
		<label for="sections[<%= index %>][input]"><?php _e( 'Sample Text Input', 'sample-repeating-metabox' ); ?></label>
		<input class="regular_text" size="30" type="text" name="sections[<%= index %>][input]" value="<%= input %>" />

		<p class="description"><?php _e( 'Sample Radio Buttons', 'sample-repeating-metabox' ); ?></p>
		<label for="sections[<%= index %>][select][alpha]"> 
			<input id="sections[<%= index %>][select][alpha]" type="radio" name="sections[<%= index %>][select]" data-default="true" value="alpha" /><?php _e( 'Alpha', 'sample-repeating-metabox' ); ?> 
		</label> 
		<label for="sections[<%= index %>][select][beta]"> 
			<input id="sections[<%= index %>][select][beta]" type="radio" name="sections[<%= index %>][select]" value="beta" /><?php _e( 'Beta', 'sample-repeating-metabox' ); ?> 
		</label>
		<label for="sections[<%= index %>][select][gamma]"> 
			<input id="sections[<%= index %>][select][gamma]" type="radio" name="sections[<%= index %>][select]" value="gamma" /><?php _e( 'Gamma', 'sample-repeating-metabox' ); ?> 
		</label><br />  

			<div class="customEditor wp-core-ui wp-editor-wrap">
				
				<div class="wp-editor-tools hide-if-no-js">

					<div id="wp-content-media-buttons" class="wp-media-buttons">
						<button type="button" id="insert-media-button" class="button insert-media add_media" data-editor="mceEditor-<%= index %>"><span class="wp-media-buttons-icon"></span><?php _e( 'Add Media', 'sample-repeating-metabox' );?></button>
					</div>

					<div class="wp-editor-tabs">
						<a data-mode="tmce" class="wp-switch-editor switch-tmce"><?php _e('Visual','generate-sections'); ?></a>
						<a data-mode="html" class="wp-switch-editor switch-html"> <?php _ex( 'Text', 'Name for the Text editor tab (formerly HTML)','generate-sections' ); ?></a>
					</div>

				</div><!-- .wp-editor-tools -->

				<div class="wp-editor-container">
					<textarea id="mceEditor-<%= index %>" name="sections[<%= index %>][textarea]" class="wp-editor-area"><%= textarea %></textarea>
				</div>

			</div>

	</div>
</div>
</script>
<!-- End template -->

</div>

<p>
	<a id="repeater-button" class="add-section button-primary" href="#"><?php _e( 'Add Section', 'sample-repeating-metabox' );?></a>
	<a id="clear-button" class="remove-all button" href="#"><?php _e( 'Remove Sections', 'sample-repeating-metabox' );?></a>
</p>