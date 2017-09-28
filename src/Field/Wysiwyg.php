<?php
/**
 * Acf codifier wysiwyg field
 */

namespace Geniem\ACF\Field;

/**
 * Class Wysiwyg
 */
class Wysiwyg extends \Geniem\ACF\Field {
    /**
     * Field type
     *
     * @var string
     */
    protected $type = 'wysiwyg';

    /**
     * What wysiwyg tabs should be shown
     *
     * @var string
     */
    protected $tabs;

    /**
     * What toolbar should be shown
     *
     * @var string
     */
    protected $toolbar;

    /**
     * Should media upload be allowed
     *
     * @var boolean
     */
    protected $media_upload;

    /**
     * Set tabs to show
     *
     * @throws \Geniem\ACF\Exception Throws error if $tabs is not valid.
     * @param string $tabs Which tabs to allow.
     * @return self
     */
    public function set_tabs( string $tabs = 'all' ) {
        if ( ! in_array( $tabs, [ 'all', 'visual', 'text' ] ) ) {
            throw new \Geniem\ACF\Exception( 'Geniem\ACF\Group: set_tabs() does not accept argument "' . $tabs . '"' );
        }

        $this->tabs = $tabs;

        return $this;
    }

    /**
     * Get allowed tabs
     *
     * @return string
     */
    public function get_tabs() {
        return $this->tabs;
    }

    /**
     * Set what toolbars should be shown
     *
     * @param string $toolbar Toolbar to show.
     * @return self
     */
    public function set_toolbar( string $toolbar = 'full' ) {
        $this->toolbar = $toolbar;

        return $this;
    }

    /**
     * Get what toolbars to show
     *
     * @return string
     */
    public function get_toolbar() {
        return $this->toolbar;
    }

    /**
     * Set whether media upload is enabled or not
     *
     * @param boolean $media_upload Should media upload be allowed.
     * @return self
     */
    public function set_media_upload( bool $media_upload ) {
        $this->media_upload = $media_upload;

        return $this;
    }

    /**
     * Get media upload state
     *
     * @return boolean
     */
    public function get_media_upload() {
        return $this->media_upload;
    }
}
