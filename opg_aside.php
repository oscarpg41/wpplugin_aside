<?php
/*
Plugin Name: OPG Aside
Plugin URI: http://www.oscarperez.es/wordpress-plugins/opg_aside.zip
Description: This aside plugin helps to manage the block aside easily over the WordPress blog. 
This db table opg_plugin_aside have six fields: idAside, name, text, url, num_order and image
Author: Oskar Pérez
Author URI: http://www.oscarperez.es/
Version: 1.0
License: GPLv2
*/
?>
<?php

    //Lo que hacemos es añadir los scripts necesarios para que el cargador de medios de wordpress se muestre
    function my_admin_scripts_aside() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_register_script('my-upload', WP_PLUGIN_URL.'/opg_aside/opg_aside.js', array('jquery','media-upload','thickbox'));
        wp_enqueue_script('my-upload');
    }
    function my_admin_styles_aside() {
        wp_enqueue_style('thickbox');
    }
    if (isset($_GET['page']) && $_GET['page'] == 'opg_aside') {
        add_action('admin_print_scripts', 'my_admin_scripts_aside');
        add_action('admin_print_styles', 'my_admin_styles_aside');
    }




    function opg_show_menu_aside(){
        add_menu_page('Oscar Pérez Plugins','Oscar Pérez Plugins','manage_options','opg_plugins','opg_plugin_aside_show_form_in_wpadmin', '', 110);
        add_submenu_page( 'opg_plugins', 'Aside', 'Aside', 'manage_options', 'opg_aside', 'opg_plugin_aside_show_form_in_wpadmin');
        remove_submenu_page( 'opg_plugins', 'opg_plugins' );        
    }
    add_action( 'admin_menu', 'opg_show_menu_aside' );


    //Hook al activar y desactivar el plugin
    register_activation_hook( __FILE__, 'opg_plugin_aside_activate' );
    //register_deactivation_hook( __FILE__, 'opg_plugin_aside_uninstall' );
    register_uninstall_hook( __FILE__, 'opg_plugin_aside_uninstall' );


    // Se crea la tabla al activar el plugin
    function opg_plugin_aside_activate() {
        global $wpdb;

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'opg_plugin_aside` 
            ( `idAside` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , 
              `name` VARCHAR( 100 ) NOT NULL , 
              `url` VARCHAR( 140 ) NOT NULL,
              `txt` VARCHAR( 255 ) NOT NULL,
              `num_order` INT ( 3 ) NOT NULL,                
              `image` VARCHAR( 140 ) NOT NULL )';
        $wpdb->query($sql);
    }

    // Se borra la tabla al desactivar el plugin
    function opg_plugin_aside_uninstall() {
        global $wpdb;
        $sql = 'DROP TABLE `' . $wpdb->prefix . 'opg_plugin_aside`';
        $wpdb->query($sql);
    }





    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
        F U N C I O N E S   D E   A C C E S O   A   B A S E   D E   D A T O S
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    //función que guarda en base de datos la información introducida en el formulario
    function opg_aside_save($name, $url, $txt, $num_order, $image)
    {
        global $wpdb;
        if (!( isset($name) && isset($url) )) {
            _e('cannot get \$_POST[]');
            exit;
        }

        $name = trim($name);
        $url  = trim($url);

        //comprobamos si empieza por http
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        $save_or_no = $wpdb->insert($wpdb->prefix . 'opg_plugin_aside', 
            array('idAside' => NULL, 'name' => esc_js(trim ($name)), 'url' => trim ($url), 'txt' => trim ($txt), 'num_order' => trim ($num_order), 'image' => $image),        
            array('%d', '%s', '%s', '%s','%d', '%s' )
        );

        if (!$save_or_no) {
            _e('<div class="updated"><p><strong>Error. Please install plugin again</strong></p></div>');
            return false;
        }
        else{
            _e('<div class="updated"><p><strong>Información del aside guardada correctamente</strong></p></div>');            
        }
        return true;
    }


    //función que borra un aside de la base de datos
    function opg_aside_remove($id)
    {
        global $wpdb;
        if ( !isset($id) ) {
            _e('cannot get \$_GET[]');
            exit;
        }

        $delete_or_no = $wpdb->delete($wpdb->prefix . 'opg_plugin_aside', array('idAside' => $id), array( '%d' ) );
        if (!$delete_or_no) {
            _e('<div class="updated"><p><strong>Error. Please install plugin again</strong></p></div>');
            return false;
        }
        else{
            _e('<div class="updated"><p><strong>Se ha borrado la información del aside</strong></p></div>');            
        }
        return true;
    }

    //función para actualizar un aside
    function opg_aside_update($id, $name, $url, $txt, $num_order, $image)
    {
        global $wpdb;
        if (!( isset($name) && isset($url) )) {
            _e('cannot get \$_POST[]');
            exit;
        }

        if ( isset($image) && (strlen($image)>0) ){
            $update_or_no = $wpdb->update($wpdb->prefix . 'opg_plugin_aside', 
                array('name' => esc_js(trim ($name)), 'url' => trim ($url), 'txt' => trim ($txt), 'num_order' => trim ($num_order), 'image' => $image),
                array('idAside' => $id),
                array('%s', '%s', '%s', '%d', '%s')
            );
        }
        else{
            $update_or_no = $wpdb->update($wpdb->prefix . 'opg_plugin_aside', 
                array('name' => esc_js(trim ($name)), 'url' => trim ($url), 'txt' => trim ($txt), 'num_order' => trim ($num_order)),
                array('idAside' => $id),
                array('%s', '%s', '%s', '%d')
            );
        }


        if (!$update_or_no) {
            _e('<div class="updated"><p><strong>Error. Please install plugin again</strong></p></div>');
            return false;
        }
        else{
            _e('<div class="updated"><p><strong>Elemento modificado correctamente</strong></p></div>');            
        }
        return true;
    }


    //función que recupera un aside usando el ID
    function opg_plugin_aside_getId($id)
    {
        global $wpdb;
        $row1 = $wpdb->get_row("SELECT name, url, txt, num_order, image  FROM " . $wpdb->prefix . "opg_plugin_aside  WHERE idAside=".$id);
        return $row1;
    }


    //función que recupera los aside guardados de la base de datos
    function opg_plugin_aside_getData()
    {
        global $wpdb;

        $asides = $wpdb->get_results( 'SELECT idAside, name, url, txt, image FROM ' . $wpdb->prefix . 'opg_plugin_aside
         ORDER BY num_order' );
        if (count($asides)>0){            
?>
            <hr style="width:94%; margin:20px 0">   
            <h2>Listado</h2>
            <table class="wp-list-table widefat manage-column" style="width:98%">            
             <thead>
                <tr>
                    <th scope="col" class="manage-column"><span>Nombre</span></th>
                    <th scope="col" class="manage-column"><span>Url</span></th>
                    <th scope="col" class="manage-column"><span>Texto</span></th>
                    <th scope="col" class="manage-column"><span>Imagen</span></th>
                    <th scope="col" class="manage-column">&nbsp;</th>
                    <th scope="col" class="manage-column">&nbsp;</th>
                </tr>
             </thead>
             <tbody>

<?php
            $cont = 0;
            foreach ( $asides as $aside ) {
                $cont++;
                if ($cont%2 ==1){ echo '<tr class="alternate">'; }
                else{ echo '<tr>'; }

?>
                    <td><?php echo( $aside->name ); ?></td>
                    <td><?php echo( $aside->url ); ?></td>
                    <td><?php echo( $aside->txt ); ?></td>
                    <td><img src="<?php echo $aside->image ?>" style="max-width: 40px"></td>
                    <td><a href="admin.php?page=opg_aside&amp;task=edit_aside&amp;id=<?php echo( $aside->idAside ); ?>"><img src="<?php echo WP_PLUGIN_URL.'/opg_aside/img/modificar.png'?>" alt="Modificar"></a></td>
                    <td><a href="#"><img src="<?php echo WP_PLUGIN_URL.'/opg_aside/img/papelera.png'?>" alt="Borrar" id="<?php echo( $aside->idAside ); ?>" class="btnDeleteAside"></a></td>
                </tr>
<?php                
            }
        }
?>
                </tbody>
            </table>
<?php
        return true;
    }



    /*
       F U N C I O N   Q U E   S E   E J E C U T A   A L   A C C E D E R   A L   P L U G I N   D E S D E   A D M I N I S T R A C I O N
       La función la definimos en la llamada add_menu_page()
    */
    function opg_plugin_aside_show_form_in_wpadmin(){
 
        $valueInputUrl   = "";
        $valueInputName  = "";
        $valueInputId    = "";


        if(isset($_POST['action']) && $_POST['action'] == 'salvaropciones'){

            //si el input idAside (hidden) está vacio, se trata de un nuevo registro
            if( strlen($_POST['idAside']) == 0 ){
                //guardamos el teléfono
                opg_aside_save($_POST['name'], $_POST['url'], $_POST['txt'], $_POST['num_order'], $_POST['upload_image']);
            }
            else{
                opg_aside_update($_POST['idAside'], $_POST['name'], $_POST['url'], $_POST['txt'], $_POST['num_order'], $_POST['upload_image']);
            }   
        }
        else{
   
            //recuperamos la tarea a realizar (edit o delete)
            if (isset($_GET["task"])){
                $task = $_GET["task"]; //get task for choosing function
            }
            else{
                $task = '';
            }
            //recuperamos el id del telefono
            if (isset($_GET["id"])){
                $id = $_GET["id"];
            }
            else{
                $id = 0;
            }


            switch ($task) {
                case 'edit_aside':
                    echo("<div class='wrap'><h2>Modificar información</h2></div>"); 

                    $row = opg_plugin_aside_getId($id);
                    $valueInputUrl   = $row->url;
                    $valueInputName  = $row->name;
                    $valueInputTxt   = $row->txt;
                    $valueInputOrder = $row->num_order;
                    $valueInputImage = $row->image;                    
                    $valueInputId    = $id;
                    break;
                case 'remove_aside':
                    opg_aside_remove($id);
                    break;
                default:
                    echo("<div class='wrap'><h2>Añadir un nuevo aside</h2></div>"); 
                    break;
            }
        }
?>
        <form method='post' action='admin.php?page=opg_aside' name='opgPluginAdminForm' id='opgPluginAdminForm' enctype="multipart/form-data">
            <input type='hidden' name='action' value='salvaropciones'> 
            <table class='form-table' style="width:95%">
                <tbody>
                    <tr>
                        <th><label for='name'>Nombre</label></th>
                        <td>
                            <input type='text' name='name' id='name' placeholder='Introduzca el nombre' value="<?php echo $valueInputName ?>" style='width: 500px'>
                        </td>
                    </tr>
                    <tr>
                        <th><label for='url'>Url</label></th>
                        <td>
                            <input type='text' name='url' id='url' placeholder='Introduzca la url' value="<?php echo $valueInputUrl ?>" style='width: 500px'>
                        </td>
                    </tr>
                    <tr>
                        <th><label for='text'>Texto</label></th>
                        <td>
                            <input type='text' name='txt' id='txt' placeholder='Introduzca el texto' value="<?php echo $valueInputTxt ?>" style='width: 500px'>
                        </td>
                    </tr>
                    <tr>
                        <th><label for='text'>Orden</label></th>
                        <td>
                            <input type='text' name='num_order' id='num_order' value="<?php echo $valueInputOrder ?>" style='width: 50px' maxlength="2" size="2">
                        </td>
                    </tr>
                    <tr>
                        <th><label for='url'>Imagen</label></th>
                        <td>
                            <input type="text" name="upload_image" id="upload_image" size='40' />
                            <input type="button" class='button-secondary' id="upload_image_button" value="Subir imagen" />                            
                        <?php 
                            if (strlen($valueInputImage)>0){
                        ?>
                            <img src="<?php echo $valueInputImage ?>" style="max-width: 30px; text-align: left; padding: 0 0 0 20px" >                         
                        <?php                                                         
                            }
                        ?>                        
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2' style='padding-left:140px'>
                            <input type='submit' value='Enviar'>
                            <input type='hidden' name="idAside" value="<?php echo $valueInputId ?>">
                        </td>
                    </tr>
                </tbody>
            </table>        
        </form>

<?php
        //se muestra el listado de todos los teléfonos guardados
        opg_plugin_aside_getData();
?>        
<?php }?>