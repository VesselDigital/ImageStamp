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
     * On new action created
     * 
     * @return void
     */
    public function __construct()
    {
        if($this->is_native) {
            add_action($this->action, array($this, 'handle'));
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
            $this->redirect( $this->base_url . '&error=nonce' );
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->redirect( $this->base_url . '&error=permission' );
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
        $form = wp_nonce_field( $this->action, '_wpnonce', true, false );
        $form .= wp_referer_field( false );
        $form .= '<input type="hidden" name="action" value="' . esc_attr("imagestamp_" . $this->action) . '" />';
        if($echo) {
            echo $form;
        } else {
            return $form;
        }
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
