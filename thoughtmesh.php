<?php
/*
Plugin Name: Thoughtmesh
Plugin URI: http://thoughtmesh.net/
Description: Connects your WordPress posts to the Thoughtmesh network
Version: 1.0
Author: John Bell and Craig Dietrich
Author URI: http://thoughtmesh.net/
License: GPLv2
*/

//Thoughtmesh essays are their own post type
function create_thoughtmesh_essay() {
    register_post_type( 'thoughtmesh_essays',
        array(
            'labels' => array(
                'name' => 'Thoughtmesh Essays',
                'singular_name' => 'Thoughtmesh Essay',
                'add_new' => 'Add Essay',
                'add_new_item' => 'Add New Thoughtmesh Essay',
                'edit' => 'Edit',
                'edit_item' => 'Edit Thoughtmesh Essay',
                'new_item' => 'New Thoughtmesh Essay',
                'view' => 'View',
                'view_item' => 'View Thoughtmesh Essay',
                'search_items' => 'Search Thoughtmesh Essays',
                'not_found' => 'No Thoughtmesh Essays found',
                'not_found_in_trash' => 'No Thoughtmesh Essays found in Trash',
                'parent' => 'Parent Thoughtmesh Essay'
            ),
 
            'public' => true,
            'menu_position' => 15,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'supports' => array( 'title', 'editor', 'excerpt', 'author', 'revisions', 'comments', 'thumbnail', 'tags' ),
            'taxonomies' => array( 'thoughtmesh_tag' ),
            'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
            'has_archive' => true
        )
    );
}

//Create the sidebar menu for thoughtmesh essays
function thoughtmesh_admin() {
    add_meta_box( 'thoughtmesh_essay_meta_box',
        'Thoughtmesh',
        'display_thoughtmesh_essay_meta_box',
        'thoughtmesh_essays', 'side', 'low'
    );
}

// Retrieve recommended set of tags for the essay and put them in the editor's tag box
function display_thoughtmesh_essay_meta_box( $movie_review ) {
    $cats = get_categories(array('hide_empty'=>0));
    ?>
    <script>
    function tagSlug(){
        var tags = jQuery('body').thoughtmesh.getLexiaTags(tinymce.activeEditor.getContent());
        console.log(tags);
        jQuery.find('#new-tag-thoughtmesh_tag')[0].value = tags.join(',');
    }
    function submitToThoughtmesh(){
        alert("This plugin is under development. This option will be activated soon!");
    }    
    </script>
    <table>
        <tr>
            <td style="width: 100%">Collection</td>
            <td>
                <select style="width: 150px" name="thoughtmesh_collection">
                <?php foreach($cats as $cat){ ?>
                    <option value="<?php echo $cat->name; ?>" selected><?php echo $cat->name; ?></option>
                <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="button" name="Get Tag Suggestions" value="Get Tag Suggestions" style="width: 100%; font-size: 150%" onclick="tagSlug()">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="button" name="Submit to Thoughtmesh" value="Submit to Thoughtmesh" style="width: 100%; font-size: 150%">
            </td>
        </tr>        
    </table>
    <?php
}

function register_thoughtmesh_taxonomy(){
    register_taxonomy('thoughtmesh_tag', 'thoughtmesh_essays', array(
        'hierarchical' => false, 
        'label' => "Tags", 
        'singular_name' => "Tag", 
        'rewrite' => true, 
        'query_var' => true
        )
    );
}

function add_thoughtmesh_essay_fields( $thoughtmesh_essay_id, $thoughtmesh_essay ) {
    // Check post type for thoughtmesh_essay
    if ( $thoughtmesh_essay->post_type == 'thoughtmesh_essays' ) {
        // Store data in post meta table if present in post data
        // if ( isset( $_POST['movie_review_director_name'] ) && $_POST['movie_review_director_name'] != '' ) {
        //     update_post_meta( $movie_review_id, 'movie_director', $_POST['movie_review_director_name'] );
        // }
        // if ( isset( $_POST['movie_review_rating'] ) && $_POST['movie_review_rating'] != '' ) {
        //     update_post_meta( $movie_review_id, 'movie_rating', $_POST['movie_review_rating'] );
        // }
    }
}

//Load the Thoughtmesh essay template for single-post viewing
//Deprecated by changing to a widget - JPB
// function include_template_function( $template_path ) {
//     if ( get_post_type() == 'thoughtmesh_essays' ) {
//         if ( is_single() ) {
//             // checks if the file exists in the theme first,
//             // otherwise serve the file from the plugin
//             if ( $theme_file = locate_template( array ( 'single-thoughtmesh_essays.php' ) ) ) {
//                 $template_path = $theme_file;
//             } else {
//                 $template_path = plugin_dir_path( __FILE__ ) . '/single-thoughtmesh_essays.php';
//             }
//         }
//     }
//     return $template_path;
// }

//Add javascript and CSS to the queue for front end posts
function thoughtmesh_register_scripts(){
    // Register the script like this for a plugin:
    wp_register_script( 'bootbox', plugins_url( '/js/bootbox.min.js', __FILE__ ), array( 'jquery' ) );
    wp_register_script( 'thoughtmesh', plugins_url( '/js/thoughtmesh.js', __FILE__ ), array( 'bootbox', 'jquery' ) );
    wp_enqueue_script('bootbox');
    wp_enqueue_script( 'thoughtmesh' );
    wp_register_style( 'thoughtmesh', plugins_url( 'thoughtmesh/css/thoughtmesh.css' ) );
    wp_enqueue_style( 'thoughtmesh' );    
}
add_action( 'wp_enqueue_scripts', 'thoughtmesh_register_scripts' );

//Add javascript and CSS to the queue for admin pages
function thoughtmesh_register_admin_scripts($hook) {
    // Load only on ?page=mypluginname
    // if($hook != 'toplevel_page_thoughtmesh') {
    //         return;
    // }
    wp_register_script( 'thoughtmesh', plugins_url( '/js/thoughtmesh.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'thoughtmesh' );        
}
add_action( 'admin_enqueue_scripts', 'thoughtmesh_register_admin_scripts' );

//Create the widget
class ThoughtMesh_Widget extends WP_Widget {
    // Main constructor
    public function __construct() {
        parent::__construct(
            'thoughtmesh_widget',
            __( 'Thoughtmesh Widget', 'thoughtmesh_text' ),
            array(
                'customize_selective_refresh' => true,
            )
        );
    }
    
    // Display the widget
    public function widget( $args, $instance ) {
        //The widget only appears on single posts of type 'thoughtmesh_essays'
        if ( get_post_type() != 'thoughtmesh_essays' || is_single() != true) {
            return;
        }

        extract( $args );
        // Check the widget options

        // WordPress core before_widget hook (always include )
        echo $before_widget;


        echo '<div id="tm_container"></div>', PHP_EOL;

        echo '    <div id="tm_footer">', PHP_EOL;
        echo '        <div id="lexia-exerpts" style="border: 1px solid black; padding: 10px;">', PHP_EOL;
        echo '            <div id="lexias-out" style="display: block;">', PHP_EOL;
                        
                            // $tags = get_terms(array('taxonomy'=>'thoughtmesh_tag'));
                            // foreach($tags as $tag){
                            //     $size = mt_rand(8, 24);
                            //     echo "<a href='#' style='font-size: ".$size."px'>$tag->name</a> ";
                            // }
                        
        echo '            </div>', PHP_EOL;
        echo '        </div>', PHP_EOL;
        echo '    </div>', PHP_EOL;

        echo '<script type="text/javascript">', PHP_EOL;
        echo '        jQuery(document).ready(function() {', PHP_EOL;
        echo '            var options = {};', PHP_EOL;
        echo '            options.externalTags = [];', PHP_EOL;
        echo '            options.platform = "wordpress";', PHP_EOL;
                        $tags = get_terms(array('taxonomy'=>'thoughtmesh_tag'));
                        foreach($tags as $tag){
                            echo "options.externalTags.push(\"$tag->name\");\n";
                        }
        echo '            jQuery("#tm_container").thoughtmesh(options);', PHP_EOL;
        echo '            console.log(options);', PHP_EOL;
        echo '        });', PHP_EOL;
        echo '    </script> ', PHP_EOL;

        // WordPress core after_widget hook (always include )
        echo $after_widget;
    }
}
// Register the widget
function register_thoughtmesh_widget() {
    register_widget( 'Thoughtmesh_Widget' );
}
add_action( 'widgets_init', 'register_thoughtmesh_widget' );


add_action( 'init', 'create_thoughtmesh_essay' );
add_action( 'init', 'register_thoughtmesh_taxonomy');
add_action( 'admin_init', 'thoughtmesh_admin' );
add_action( 'save_post', 'add_thoughtmesh_essay_fields', 10, 2 );
add_filter( 'template_include', 'include_template_function', 1 );

?>