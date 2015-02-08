<?php
class MAPSearchBox {

	public function __construct() {

		if ( is_admin() ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			add_action( 'do_meta_boxes', array( &$this, 'addMetaBox' ), 10, 2 );
		}

	}

	public function getPosts() {

		global $wpdb;

		$posts = array();

		$query  = "SELECT ID, post_title FROM {$wpdb->posts} ";
		if ( $this->post_type && isset( $_GET['post'] ) && isset( $_GET['action'] ) ) {
			$query .= "WHERE post_type = %s ";
		} else {
			$query .= "WHERE post_type != 'revision' ";
		}

		$post_results = $wpdb->get_results( $wpdb->prepare( $query, $this->post_type ) );
		if ( $post_results ) {
			foreach ( $post_results as $post_result ) {
				if ( $post_result->post_title ) {
					$posts[] = array( 'label' => 'ID = ' . $post_result->ID  . ' - ' . $post_result->post_title, 'value' => admin_url( 'post.php?post=' . $post_result->ID . '&action=edit' ) );
				}
			}
		} else {
			$posts[] = array( 'label' => 'Nothing Found', 'value' => '' );
		}
		return json_encode( $posts );

	}

	public function displaySearchBox() {

		global $post;
		$this->post_type = $post->post_type;
		$this->posts = $this->getPosts();
		?>
		<script type="text/javascript">
		jQuery(document).ready(function(){

			var availablePosts = <?php echo $this->posts; ?>;
			jQuery('#posts_search').autocomplete({
				source: availablePosts,
		        select: function( event, ui ) {
		            window.location.href = ui.item.value;
		        }
			});

		});
		</script>
		<input type="text" name="posts_search" id="posts_search" style="width: 100%;">
		<?php

	}

	public function addMetaBox( $page, $context ) {

		add_meta_box( 'map-posts-search', 'Search', array( &$this, 'displaySearchBox' ), $page, 'side', 'high' );

	}

}

function map_search_box() {
	$map_search_box = new MAPSearchBox();
}
?>