<div class="wrap">
	<h2>Advanced Spintax Post Generator <small>v0.1.0</small></h2>
    
	<noscript><h2><br>Error! Javascript is required!</h2></noscript>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top"><?php echo esc_html__('Post Title', 'aspgspintax'); ?> <small><?php echo esc_html__('(spintax enabled)', 'aspgspintax'); ?></small></th>
				<td>
					<input type="text" id="post-title" value="{{The top|Top} 10|10 {top|best}} {reasons|reasons|features|products} {to get|for} X">
				</td>
			</tr>


			<tr>
				<th scope="row" valign="top"><?php echo esc_html__('Post Content', 'aspgspintax'); ?> <small><?php echo esc_html__('(spintax enabled)', 'aspgspintax'); ?></small></th>
				<td>
					<textarea id="post-content">This is {an {example|article}|a {test|post}}{!|.}</textarea>
				</td>
			</tr>


			<tr>
				<td>
					<h3><?php echo esc_html__('Post Meta (advanced)', 'aspgspintax'); ?></h3>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="top"><?php echo esc_html__('Slug', 'aspgspintax'); ?> <small><?php echo esc_html__('(spintax enabled, comma-separated)', 'aspgspintax'); ?></small></th>
				<td>
					<input type="text" id="post-slug">
				</td>
			</tr>

			<tr>
				<th scope="row" valign="top"><?php echo esc_html__('Tags', 'aspgspintax'); ?> <small><?php echo esc_html__('(spintax enabled, comma-separated)', 'aspgspintax'); ?></small></th>
				<td>
					<input type="text" id="post-tags">
				</td>
			</tr>


			<tr>
				<th scope="row" valign="top"><?php echo esc_html__('Categories', 'aspgspintax'); ?> <small><?php echo esc_html__('(spintax enabled, comma-separated)', 'aspgspintax'); ?></small></th>
				<td>
					<input type="text" id="post-categories">
				</td>
			</tr>


			<tr>
				<th scope="row" valign="top"><?php echo esc_html__('Post Type', 'aspgspintax'); ?> <small><?php echo esc_html__('(post/page/custom post type)', 'aspgspintax'); ?></small></th>
				<td>
					<input type="text" id="post-type" value="post" placeholder="post">
				</td>
			</tr>




			<tr>
				<td>
					<span><?php echo esc_html__('Result', 'aspgspintax'); ?>: <span id="aspg-result"></span></span>
				</td>
			</tr>


			<tr>
				<td>
					<button class="button-primary" onclick="aspg_generate_post()" id="generate-button"><?php echo esc_html__('Generate Post', 'aspgspintax'); ?></button>
				</td>
			</tr>
		</tbody>
	</table>

	<script>
		function aspg_generate_post() {
			jQuery('#generate-button').attr("disabled", true); //disable button
			jQuery('#aspg-result').html(''); //clear text

			try {
				var data = {
					'security': '<?php echo wp_create_nonce($GLOBALS['aspgspintax_nonce']); ?>',

					'action': 'aspgspintax_create_post',
					'post_type': jQuery('#post-type').attr("value"),
					'title': jQuery('#post-title').attr("value"),
					'content': jQuery('#post-content').attr("value"),
					'status': 'publish',
					'slug': jQuery('#post-slug').attr("value"),
					'categories': jQuery('#post-categories').attr("value"),
					'tags': jQuery('#post-tags').attr("value"),
					
				};
		
				jQuery.post(ajaxurl, data, aspg_generate_callback).fail(aspg_failure_callback);
			}
			catch(ex) {
				jQuery('#generate-button').attr("disabled", false); //re-enable button
				
			}

		}

		function aspg_failure_callback(message) {
			if(message == null || message.responseText == '0' || Array.isArray(message)) message = '<?php echo esc_html__('Post failed, unknown error', 'aspgspintax'); ?>';
			jQuery('#generate-button').attr("disabled", false); //re-enable button
			jQuery('#aspg-result').html('<span style="color:#FF1505">'+message+'</span>');
		}

		function aspg_generate_callback(response) {
			jQuery('#generate-button').attr("disabled", false); //re-enable button

			if(response.success != 1) {
				aspg_failure_callback(response.message);
				return;
			}

			
			jQuery('#aspg-result').html('<span style="color:#09901a">'+response.message+'</span> <a href="<?php echo admin_url('post.php') ?>?post='+response.ID+'&action=edit" target="_blank"><?php echo esc_html__('Click here to view the post (new window)', 'aspgspintax'); ?></a>');
			//debugger;
		}
	</script>

	<style>
		textarea { width: 100%; max-width: 900px; min-height: 150px; }
		input[type="text"] { width: 100%; max-width: 900px; }
	</style>

</div>