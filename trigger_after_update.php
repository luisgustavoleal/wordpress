
add_action( 'save_post', 'cb_set_excerto' );


/*
 * Função que atualiza o campo Excerpt com o primeiro paragrafo do texto (corpo do post), executada a cada insert/update do post. 
 */

function cb_set_excerto($post_id) {

	global $post;

	// If this is a revision, get real post ID
	if ( $parent_id = wp_is_post_revision( $post_id ) ) 
	$post_id = $parent_id;

	// Get the first paragraph
	$excerpt = get_first_paragraph($post->ID);

	// ***
	remove_action( 'save_post', 'cb_set_excerto' );

	$data_content = $_POST['description'];

	// Parameters
	$my_post = array();
	$my_post['ID'] = $post->ID;
	$my_post['post_excerpt'] = $excerpt;
	// Update table
	wp_update_post( $my_post );

	// ***
	add_action( 'save_post', 'cb_set_excerto' );

	// *** Remove e add action para evitar looping

}


/*
 *  Funcao para pegar o primeiro paragrafo do texto (corpo) de um post
 */

function get_first_paragraph($_id_post){
	
	$post = get_post($_id_post);

	$text = $post->post_content;

	$start = strpos($text, '<p>'); // Locate the first paragraph tag
	$end  = strpos($text, '</p>', $start); // Locate the first paragraph closing tag
	$text = substr($text, $start, $end-$start+4); // Trim off everything after the closing paragraph tag
	$text = strip_shortcodes( $text ); // Remove shortcodes
	$text = apply_filters('the_content', $text); // remove tag html by wp
	$text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);	 // Remove tag script
	$text = wpautop( $text );	// Remove break
	$text = strip_tags($text);	// Remove tags html

	return $text ;
	   
}


