<?php

class FCS_Core {

    public $helper;

    public function __construct() {
        $this->do_shortcode();
        $this->do_actions();
        $this->helper = new FCF_Helper();
    }

    private function do_actions() {
        add_action('init', array($this, 'init'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
        add_action('save_post', array($this, 'save_post'));
    }

    private function do_shortcode() {
        add_shortcode('fcf', array('FCF_Helper', 'fcf_generate_form_shortcode_out'));
    }

    public function init() {
        $this->register_contact_form();
        $this->form_submiting();
        $this->need_message();
        $this->email_process();
    }

    public function email_process() {
        if (isset($_POST['action']) && $_POST['action'] == 'fcf_email_process') {
            $emails = filter_input(INPUT_POST, 'emails');
            $form_id = filter_input(INPUT_POST, 'form_id', FILTER_VALIDATE_INT);
            $emails = explode(',', $emails);
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    $this->auto_responder($email, $form_id);
                }
            }
            die();
        }
    }


   public function add_meta_boxes() {
        $this->form_generate_add_contact_meta();
        $this->shortcode_meta_box();
    }

    public function wp_enqueue_scripts() {
        wp_register_script('ajax-form', FCF_URL . '/resources/jquery.form.min.js');
        wp_register_script('modernizr', FCF_URL . '/resources/modernizr.js');
        wp_enqueue_script('webshim', FCF_URL . '/resources/js-webshim/polyfiller.js');
        wp_enqueue_script('fcf-functions-js', FCF_URL . '/resources/functions.js', array('jquery', 'modernizr', 'webshim', 'ajax-form'));
    }

    public function admin_enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_style('form-builder-css', FCF_URL . '/resources/formbuilder.css');
        wp_enqueue_style('form-required-css', FCF_URL . '/resources/css/vendor.css');
        wp_register_script('form-required-js', FCF_URL . '/resources/js/vendor.js');
        wp_register_script('form-builder-js', FCF_URL . '/resources/formbuilder-min.js', array('form-required-js'));
        wp_enqueue_script('fcf-admin-functions-js', FCF_URL . '/resources/admin-functions.js', array('jquery', 'form-builder-js'));
    }

    public function save_post($post_id) {
        if (wp_is_post_revision($post_id))
            return;
        if (wp_is_post_autosave($post_id))
            return;
        $form_code = filter_input(INPUT_POST, 'form_code');
        update_post_meta($post_id, 'form_code', $form_code);
        $submit_txt = filter_input(INPUT_POST, 'submit_txt');
        update_post_meta($post_id, 'submit_txt', $submit_txt);
        $redirect_url = filter_input(INPUT_POST, 'redirect_url');
        update_post_meta($post_id, 'redirect_url', $redirect_url);
        $responder_attachment = filter_input(INPUT_POST, 'responder_attachment');
        update_post_meta($post_id, 'responder_attachment', $responder_attachment);

        if (isset($_POST['messages'])) {
            $messages = $_POST['messages'];
            $messages = array_map('sanitize_text_field', $messages);
            update_post_meta($post_id, 'messages', $messages);
        }
    }

    private function register_contact_form() {
        $labels = array(
            'name' => ' Forms Builder',
            'singular_name' => 'Form Builder',
            'menu_name' => 'Form Builder',
            'name_admin_bar' => 'Form Builder',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Form',
            'new_item' => 'New Form',
            'edit_item' => 'Edit Form',
            'view_item' => 'View Form',
            'all_items' => 'All Forms',
            'search_items' => 'Search Forms',
            'not_found' => 'No forms found',
            'not_found_in_trash' => 'No forms found in Trash'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'supports' => array('title')
        );

        register_post_type('first_contact', $args);
    }

    private function form_generate_add_contact_meta() {
        add_meta_box('fcf_post_meta', " Form Builder", array($this->helper, 'form_generate_post_meta_box_output'), 'first_contact');
    }

    private function shortcode_meta_box() {
        add_meta_box('fcf_shortcode', "Form Settings", array($this->helper, 'shortcode_fcf_shortcode_out'), 'first_contact', 'side');
    }

    private function form_submiting() {
        if (isset($_POST['action']) && $_POST['action'] == 'fcf_submit') {
            unset($_POST['action']);
            add_shortcode('fcf', array('FCF_Helper', 'mail_form_shortcode'));
            $post_id = (int) filter_input(INPUT_POST, 'form_id');
            $messages = get_post_meta($post_id, 'messages', TRUE);
            $mail_content = get_post_meta($post_id, 'mail_content', TRUE);
            $mail_content = do_shortcode($mail_content);
            $attachments = array();
            foreach ($_FILES as $key => $file) {
                if (!empty($file['name'])) {
                    //Uploading File
                    if (!function_exists('wp_handle_upload'))
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    $uploadedfile = $file;
                    $upload_overrides = array('test_form' => false);
                    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
                    if ($movefile) {
                        $attachments[] = $movefile['file'];
                    } else {
                        $message = array(
                            'type' => 'error',
                            'message' => $messages['failed_upload'],
                        );
                        echo "Possible file upload attack!\n";
                        die();
                    }
                }
            }
            //Mail sending
            add_filter('wp_mail_content_type', array('FCF_Helper', 'set_html_content_type'));
            $mail_to = get_post_meta($post_id, 'user_email', TRUE);
            $post = get_post($post_id);
            $domain_name = preg_replace('/^www\./', '', $_SERVER['SERVER_NAME']);
            $headers[] = "From: First Contact <first_contact@$domain_name>" . PHP_EOL;
            $result = wp_mail($mail_to, $post->post_title, $mail_content, $headers, $attachments);
            remove_filter('wp_mail_content_type', array('FCF_Helper', 'set_html_content_type'));
            echo $result;
            die();
        }
    }

    function need_message() {
        if (isset($_POST['action']) && $_POST['action'] == 'fcf_get_messages') {
            $form_id = $_POST['id'];
            $message = get_post_meta($form_id, 'messages', TRUE);
            echo json_encode($message);
            die();
        }
    }

}
