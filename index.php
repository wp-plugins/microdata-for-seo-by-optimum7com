<?php
/*
    Plugin Name: Microdata for Wordpress
    Plugin URI: http://jbeaujardin.com/product/plugins/generic-custom-type
    Description:This plugins allows you to add Microdata elements to your pages and posts on your blog.
    Version: 2.0
    Author: Optimum7
    Author URI: http://optimum7.com
    Copyright 2011  Optimum7 Inc  (email : julian@optimum7.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(plugin_dir_path(__FILE__).'classes/types.php');
require_once(plugin_dir_path(__FILE__).'classes/classes.php');

register_activation_hook( __FILE__, array('microdata_for_wordpress', 'activation') );
register_deactivation_hook( __FILE__, array('microdata_for_wordpress', 'deactivation') );
add_shortcode( 'Microdata', array('microdata_for_wordpress','short_code'));

add_action('admin_enqueue_scripts', array('microdata_for_wordpress','admin_enqueue_scripts'));
add_action('media_buttons', array('microdata_for_wordpress','addButton'),30);
add_action('admin_init',array('microdata_for_wordpress','add_microdata_iframe_content'));
add_action('admin_footer',array('microdata_for_wordpress','add_microdata_plugin_footer'));
add_action('wp_ajax_crate_option', array('microdata_for_wordpress','crate_option_callback'));
add_action('wp_ajax_update_option', array('microdata_for_wordpress','update_option_callback'));
add_action('wp_ajax_load_classes_option', array('microdata_for_wordpress','load_classes_option_callback'));
add_action('wp_ajax_load_schema_option', array('microdata_for_wordpress','load_schema_option_callback'));
add_action('wp_ajax_load_html5_option', array('microdata_for_wordpress','load_html5_option_callback'));
add_action('wp_ajax_eval_microdata_option', array('microdata_for_wordpress','eval_microdata_option_callback'));

class microdata_for_wordpress{
    function activation(){
        self::set_options();
    }
    function set_options(){
      //  update_option('1','<a>Test</a>');
    }
    function deactivation(){
        delete_option('1');
    }
    function admin_enqueue_scripts(){

    }
    function short_code($atts){
        extract( shortcode_atts( array(
            'id' => '',
            'schema'=>''
        ), $atts ) );
        $object = get_option($id);
        return $object->microdata();
    }
    function addButton(){
       global $post_ID, $temp_ID;
       $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
       $media_upload_iframe_src = get_option('siteurl').'/wp-admin/media-upload.php?post_id='.$uploading_iframe_ID;
       $media_amd_microdata_iframe_src = apply_filters('media_amd_microdata_iframe_src', "$media_upload_iframe_src&amp;type=amd_microdata&amp;tab=amd_microdata");
       $media_amd_microdata_title = __('Microdata for Wordpress', 'wp-media-amd_microdata');
        echo "<a id='microdata-button' class=\"thickbox\" href=\"{$media_amd_microdata_iframe_src}&amp;TB_iframe=true\" title=\"$media_amd_microdata_title\"><img src='" . plugins_url( 'images/icon.gif' , __FILE__ ) . "' ></a>";
    }
    function add_microdata_iframe_content(){
        if (strpos($_SERVER['REQUEST_URI'], 'media-upload.php') && strpos($_SERVER['REQUEST_URI'], '&type=amd_microdata') && !strpos($_SERVER['REQUEST_URI'], '&wrt=')){
            $url = get_option('siteurl');
            $dirname = dirname(plugin_basename(__FILE__));

            echo <<< HTML
		<!DOCTYPE html>
		<headxmlns="http://www.w3.org/1999/html">
   	        <link rel="stylesheet" href="$url/wp-content/plugins/$dirname/style.css" type="text/css" media="all" />
   	        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
            <script type="text/javascript" src="$url/wp-content/plugins/$dirname/js/microdata_iframe.js"></script>
            <script type="text/javascript" src="$url/wp-admin/js/media-upload.js"></script>
            <script type="text/javascript" src="$url/wp-includes/js/thickbox/thickbox.js"></script>
        </head>
        <body id="mfw-microdata-uploader">
            <div id='main' style="margin:20px;">
            <div id='caption'>
              <legend>Microdata For Wordpress by<a title="Optimum7.com" href="http://www.optimum7.com/" target="_blank"><img style="width: 200px;height: 40px;vertical-align: bottom; " src='$url/wp-content/plugins/$dirname/images/opt7-logo.png' alt="test"/></a></legend>

            </div>
                 <div id='Opt7-microdata-admin-selectbox'>
                    <label id='lable-schemas-classes' for="schemas-classes">Microdata Schemas:</label>
                    <select id="schemas-classes">
                    </select>
                 </div>
                 <div id="middle-notify" class="" style="text-align:center">
                    <img src='images/loading.gif' alt="loading"/>
                </div>
                 <div id="table" style="margin-top:0px;height:auto"></div>
                 <textarea class="" style="height: 160px; width: 360px; resize: none;" name="html5" id="html5"></textarea><br>
                 <input type="submit" name="submit" id="save-option" value="Save Changes">
                 <input type="submit" name="submit" id="cancel-option" value="Cancel">
            </div>
        </body>
HTML;
            exit;
        }
    }
    function add_microdata_plugin_footer() {
        $url = get_option('siteurl');
        $dirname = dirname(plugin_basename(__FILE__));
        echo <<< HTML
                <script>//<![CDATA[
                    var baseurl = '$url';
                    var dirname = '$dirname';
                    var text='';
                    jQuery('#content').select(function(){
                            var selectedText = document.getSelection();
                            text = selectedText.toString();
                    });
                    function amdMicrodataInsertIntoPostEditor(microdata) {
                        var ed;
                        if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
                                ed.focus();
                                if ( tinymce.isIE )
                                ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
                                ed.execCommand('mceInsertContent', false, '['+microdata+']');
                        }
                        else if ( typeof edInsertContent == 'function' ) {
                                 edInsertContent(edCanvas, '['+microdata+']');
                        }
                        else {
                                jQuery( edCanvas ).val( jQuery( edCanvas ).val() +  '['+microdata+']' );
                        }
                    }
          //]]></script>
HTML;
    }
    function crate_option_callback(){
        $properties= json_decode(str_replace ('\"','"', $_POST['properties']), true);
        $class= $_POST['class'];
        $object = self::createObject($class,$properties);
        update_option($object->id,$object);
        $short_code = array();
        $short_code['id']=$object->id;
        $short_code['schema']=$_POST['class'];
        echo json_encode($short_code);
        die();
    }
    function update_option_callback(){
        $properties= json_decode(str_replace ('\"','"', $_POST['properties']), true);
        $class= $_POST['class'];
        $object = self::createObject($class,$properties);
        update_option($object->id,$object);
        echo json_encode($object);
        die();
    }
    function load_classes_option_callback(){
        $php_file = file_get_contents(plugin_dir_path(__FILE__).'classes/types.php');
        $tokens = token_get_all($php_file);
        $class_token = false;
        $classes = array();
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] == T_CLASS) {
                    $class_token = true;
                } else if ($class_token && $token[0] == T_STRING) {
                    $classes[] =$token[1];
                    $class_token = false;
                }
            }
        }
        echo json_encode($classes);
        die();
    }
    function load_schema_option_callback($type=null,&$properties=array()){
        if (!$type)  $type = $_POST['type'];
        require_once(plugin_dir_path(__FILE__).'classes/types.php');
        require_once(plugin_dir_path(__FILE__).'classes/classes.php');
        $object = new $type();
        $vars = get_object_vars($object);
        foreach ($vars as $key=>$var){
           if (is_object($var)){
                self::load_schema_option_callback($var,$properties);
           }else{
               $properties[$key]=$var;
           }
        }
        echo json_encode($properties);
        die();
    }
    function load_html5_option_callback(){
        $properties= json_decode(str_replace ('\"','"', $_POST['properties']), true);
        $class = $_POST['class'];
        $object = self::createObject($class,$properties);
        echo json_encode($object->microdata());
        die();
    }
    function createObject($class,$properties){
        $object = new $class();
        foreach ($properties as $name=>$value){
            $object->$name=$value;
        }
        return $object;
    }
    function eval_microdata_option_callback(){

        $cadena = str_replace ('\"','"', $_POST['cadena']);
        $cadena = self::extraer_short_code("[","]",$cadena);
        $retorno = array();

        if($cadena != false){

            $split = split(' ', $cadena);

            if( $split[0] == 'Microdata'){

                $id = explode('=', $split[1],2);
                $object = get_option($id[1]);
                $type = get_class($object);
                $retorno['object'] = $object;
                $retorno['type'] = $type;
                $retorno['status']='success';
                echo json_encode($retorno);
            }
            else{
                $retorno['status']='error';
                echo json_encode($retorno);
            }
        }
        else
        {
            $retorno['status']='errorrr';
            echo json_encode($retorno);
        }
        die();
    }
    function extraer_short_code($separador1,$separador2,$cadena){

        if(strpos($cadena,$separador1)!==false)
        {
            $pos=strpos($cadena,$separador1);
            $a=substr($cadena,$pos+strlen($separador1));
            if(strpos($a,$separador2)!==false)
            {
                $npos=strpos($a,$separador2);
                $b=substr($a,0,$npos);
                return $b;
            }
            else
                return false;
        }
        else
            return false;

    }
}




