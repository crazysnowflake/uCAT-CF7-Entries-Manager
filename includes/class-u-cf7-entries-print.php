<?php
/**
 * print Class
 *
 *
 * @author      uCAT
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * U_CF7_Entries_Print class.
 */
class U_CF7_Entries_Print {

	private $entry_id     = 0;
	private $properties   = array();
	private $posted_data  = array();
	private $contact_form = null;

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		$entry_id = isset($_GET['entry']) && !empty($_GET['entry']) ? $_GET['entry'] : false;
		if (  current_user_can( WPCF7_ADMIN_READ_CAPABILITY ) && $entry_id) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_print' ) );

			$this->entry_id     = $entry_id;
			$this->properties   = get_post_meta( $entry_id, '_properties', true );
			$this->posted_data  = $posted_data = get_post_meta( $entry_id, '_posted_data', true );
			$this->contact_form = wpcf7_contact_form( $this->posted_data['_wpcf7'] );		

		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'u_cf7_entries-print', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_print() {
		if ( empty( $_GET['page'] ) || 'u_cf7_entries-print' !== $_GET['page'] ) {
			return;
		}
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );
		wp_enqueue_script('utils');
		wp_enqueue_script( 'svg-painter' );
		wp_enqueue_style( U_CF7_Entries()->_token . '-print', esc_url( U_CF7_Entries()->assets_url ) . 'css/print.css', array(), U_CF7_Entries()->_version );
		wp_register_script( U_CF7_Entries()->_token . '-print', esc_url( U_CF7_Entries()->assets_url ) . 'js/print.js', array('jquery'), U_CF7_Entries()->_version  );

		ob_start();
		$this->setup_print_header();
		$this->setup_print_content();
		$this->setup_print_footer();
		exit;
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_print_header() {
		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php _e( 'Print Preview: ', 'u-cf7-entries' ); echo $this->contact_form->title(); ?></title>
			<?php wp_print_scripts( U_CF7_Entries()->_token . '-print' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="u-cf7-entries-print wp-core-ui">
			<div class="wrap">
			<h1>
				<span>
					<?php _e( 'Print Preview:', 'u-cf7-entries' ); ?> 
				</span>
				<?php echo $this->contact_form->title(); ?>
				- 
				<?php printf(__('Entry #%d', 'u-cf7-entries'), $this->entry_id) ?>
				</h1>
			<div class="wp-filter">
				<ul>
					<li>
						<label>
							<input type="checkbox" id="include_submission" checked="checked">
							<?php _e( 'Include submission', 'u-cf7-entries' ); ?>
						</label>						
					</li>
					<li>
						<label>
							<input type="checkbox" id="include_comments" checked="checked">
							<?php _e( 'Include comments', 'u-cf7-entries' ); ?>
						</label>						
					</li>
					<li class="actions">
						<a onclick="window.print();" href="javascript:;"><?php _e( 'Print', 'u-cf7-entries' ); ?></a>
					</li>
				</ul>
			</div>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_print_footer() {
		?>			
			</div> <!-- /.wrap -->
			</body>
		</html>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_print_content() {
		if( $this->contact_form ){
			$tags = $this->contact_form->form_scan_shortcode();	
		}
		?>
		<div id="submission" class="postbox">
			<table class="wp-list-table widefat striped">
				<tbody>
				<?php if($this->contact_form && $tags): ?>
					<?php foreach ($tags as $tag): if( $tag['basetype'] == 'submit' ) continue; $tag = new WPCF7_Shortcode( $tag ); ?>
					<tr>
						<td class="manage-column column-field">
							<?php echo $tag->name; ?>	
						</td>
						<td class="manage-column column-value">
							<?php
							$tag_data = '';
							if( isset($this->posted_data[$tag->name])) {
								$tag_data = $this->posted_data[$tag->name];
								unset($this->posted_data[$tag->name]);
							}
								//var_dump($tag);
								$value = is_array($tag_data) ? implode(', ', $tag_data) : $tag_data;
								echo esc_textarea( $value );
							?>	
						</td>
					</tr>
					<?php endforeach; ?>							
				<?php endif;
				$hidden = array();
				if( count($this->posted_data) > 0 ): ?>
					<?php foreach ($this->posted_data as $key => $data): ?>
					<?php if( strrpos($key, '_wpcf7') === 0 || strrpos($key, '_u_cf7') === 0 ){ $hidden[$key] = $data;  continue; } ?>
					<tr>
						<td class="manage-column column-field">
							<?php echo $key; ?>	
						</td>
						<td class="manage-column column-value">
							<?php
								$value = is_array($data) ? implode(', ', $data) : $data;
								echo esc_textarea( $value );
							 ?>
						</td>
					</tr>
					<?php endforeach; ?>					
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div id="commentsdiv" class="postbox ">
			<h2 class="hndle ui-sortable-handle">
			<?php _e( 'Comments', 'u-cf7-entries' ); ?>
			</h2>
			<div class="inside" id="activity-widget">
			<?php
			$comments_query = array(
				'offset' => 0,
				'post_id' => $this->entry_id
			);
			if ( ! current_user_can( WPCF7_ADMIN_READ_WRITE_CAPABILITY ) )
				$comments_query['status'] = 'approve';

			$comments = get_comments( $comments_query );
			if ( $comments ) {
				echo '<div id="latest-comments" class="activity-block">';

				echo '<ul id="the-comment-list" data-wp-lists="list:comment">';
				foreach ( $comments as $comment ) :
					//$GLOBALS['comment'] = clone $comment;
					
					if ( $comment->comment_post_ID > 0 ) {

						$comment_post_title = _draft_or_post_title( $comment->comment_post_ID );
						$comment_post_url   = get_the_permalink( $comment->comment_post_ID );
						$comment_post_link  = "<a href='$comment_post_url'>$comment_post_title</a>";
					} else {
						$comment_post_link = '';
					}
					?>
					<li id="comment-<?php echo $comment->comment_ID; ?>" <?php comment_class( array( 'comment-item', wp_get_comment_status( $comment ) ), $comment ); ?>>

						<?php echo get_avatar( $comment, 50, 'mystery' ); ?>

						<?php if ( !$comment->comment_type || 'comment' == $comment->comment_type ) : ?>

						<div class="dashboard-comment-wrap has-row-actions">
						<p class="comment-meta">
						<?php
							// Comments might not have a post they relate to, e.g. programmatically created ones.
							if ( $comment_post_link ) {
								printf(
									/* translators: 1: comment author, 2: post link, 3: notification if the comment is pending */
									__( 'From %1$s on %2$s %3$s' ),
									'<cite class="comment-author">' . get_comment_author_link( $comment ) . '</cite>',
									$comment_post_link,
									'<span class="approve">' . __( '[Pending]' ) . '</span>'
								);
							} else {
								printf(
									/* translators: 1: comment author, 2: notification if the comment is pending */
									__( 'From %1$s %2$s' ),
									'<cite class="comment-author">' . get_comment_author_link( $comment ) . '</cite>',
									'<span class="approve">' . __( '[Pending]' ) . '</span>'
								);
							}
						?>
						</p>

						<?php
						else :
							switch ( $comment->comment_type ) {
								case 'pingback' :
									$type = __( 'Pingback' );
									break;
								case 'trackback' :
									$type = __( 'Trackback' );
									break;
								default :
									$type = ucwords( $comment->comment_type );
							}
							$type = esc_html( $type );
						?>
						<div class="dashboard-comment-wrap has-row-actions">
						<p class="comment-meta">
						<?php
							// Pingbacks, Trackbacks or custom comment types might not have a post they relate to, e.g. programmatically created ones.
							if ( $comment_post_link ) {
								printf(
									/* translators: 1: type of comment, 2: post link, 3: notification if the comment is pending */
									_x( '%1$s on %2$s %3$s', 'dashboard' ),
									"<strong>$type</strong>",
									$comment_post_link,
									'<span class="approve">' . __( '[Pending]' ) . '</span>'
								);
							} else {
								printf(
									/* translators: 1: type of comment, 2: notification if the comment is pending */
									_x( '%1$s %2$s', 'dashboard' ),
									"<strong>$type</strong>",
									'<span class="approve">' . __( '[Pending]' ) . '</span>'
								);
							}
						?>
						</p>
						<p class="comment-author"><?php comment_author_link( $comment ); ?></p>

						<?php endif; // comment_type ?>
						<blockquote><p><?php comment_excerpt( $comment ); ?></p></blockquote>
						</div>
					</li>
					<?php
				endforeach;
				echo '</ul>';
				echo '</div>';
			}
			?>
			</div>
		</div>
		<?php
	}

	
}

new U_CF7_Entries_Print();
