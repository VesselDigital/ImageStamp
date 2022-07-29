<?php
namespace ImageStamp\Actions;
/**
 * Action base page class
 */

class Action
{

    /**
     * The action name
     * 
     * @var string
     */
    public $action;

    /**
     * The base page url
     * 
     * @var string
     */
    public $base_url;

    /**
     * The action's data
     * 
     * @var array
     */
    public $data;

    /**
     * Is a native wordpress action?
     * 
     * @var boolean
     */
    public $is_native = false;

    /**
     * Is an ajax action?
     * 
     * @var boolean
     */
    public $is_ajax = false;


    /**
     * On new action created
     * 
     * @return void
     */
    public function __construct()
    { 
        if($this->is_native) {
            add_action($this->action, array($this, 'handle'));
        } if($this->is_ajax) {
            add_action( 'wp_ajax_imagestamp_' . $this->action, [$this, '_handle'] );
        } else {
            add_action( 'admin_post_imagestamp_' . $this->action, [$this, '_handle'] );
        }
    }

    /**
     * Run some checks on the action before actually handling it.
     * 
     * @return void
     */
    public function _handle() {
        $nonce = $_REQUEST["_wpnonce"];

        if ( ! wp_verify_nonce( $nonce, $this->action ) ) {
            if($this->is_ajax) {
                wp_send_json_error([ "message" => "Invalid token" ]);
            } else {
                $this->redirect( $this->base_url . '&error=nonce' );
            }
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            if($this->is_ajax) {
                wp_send_json_error([ "message" => "You don't have permission to access this" ]);
            } else {
                $this->redirect( $this->base_url . '&error=permission' );
            }
            return;
        }

        $this->data = $_REQUEST;
        $this->handle();
    }

    /**
     * Redirect to the page with the given slug
     * 
     * @param string $slug The slug of the page to redirect to
     * @return void
     */
    public function redirect( $url ) {
        wp_redirect( $url );
        exit;
    }

    /**
     * Get the form HTML with action and nonce fields
     * 
     * @param boolean $echo
     * @return string|void
     */
    public function get_form($echo = false) {
        $form = $this->get_nonce_field();
        $form .= wp_referer_field( false );
        $form .= '<input type="hidden" name="action" value="' . esc_attr("imagestamp_" . $this->action) . '" />';
        if($echo) {
            echo $form;
        } else {
            return $form;
        }
    }

    /**
     * Get the action nonce
     * 
     * @param string $name
     * @return string|void
     */
    public function get_nonce_field(string $name = "_wpnonce") {
        return wp_nonce_field( $this->action, $name, true, false );
    }


    /**
     * Handle the action
     * 
     * @return void
     */
    public function handle() {
        // 
    }
}
