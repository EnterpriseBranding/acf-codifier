<?php
/**
 * ACF Codifier Groupable group abstract class
 */

namespace Geniem\ACF\Field;

/**
 * Groupable Class
 */
class Groupable {
    /**
     * Instance of the class that inherited Groupable.
     *
     * @var mixed
     */
    protected $inheritee;

    /**
     * Variable name for the fields storage
     *
     * @var string
     */
    protected $fields_var = 'fields';

    /**
     * Either inheritee or $this
     *
     * @var mixed
     */
    private $self;

    /**
     * Constructor.
     *
     * @param mixed $inheritee Instance of the class that inherited this class.
     */
    public function __construct( $inheritee = null ) {
        // Save the inheritee class into its property.
        $this->inheritee = $inheritee;

        if ( isset( $this->inheritee ) && property_exists( $this->inheritee, 'sub_fields' ) ) {
            $this->fields_var = 'sub_fields';
        }
        else {
            $this->fields_var = 'fields';
        }

        $this->self = $this->inheritee ?? $this;
    }

    /**
     * __get
     *
     * Reference allows the referenced property to be modified through
     * the call.
     *
     * @param string $name Name of the property to get.
     * @return mixed
     */
    public function &__get( $name ) {
        // Return inheritee's property with the asked name.
        return $this->inheritee->{ $name };
    }

    /**
     * Update the self reference to be up to date after cloning.
     *
     * @return void
     */
    public function update_self( $self ) {
        $this->self = $self;
    }

    /**
     * Export current field and sub fields to acf compatible format
     *
     * @param boolean $register Whether the field is to be registered.
     *
     * @return array
     */
    public function export( $register = false ) {
        $obj = get_object_vars( $this );

        // Remove unnecessary properties from the exported array.
        unset( $obj['inheritee'] );
        unset( $obj['groupable'] );
        unset( $obj['fields_var'] );
        unset( $obj['filters'] );

        // Loop through fields and export them.
        if ( ! empty( $obj[ $this->fields_var ] ) ) {
            foreach ( $obj[ $this->fields_var ] as $field ) {
                if ( $field instanceof \ Geniem\ACF\Field\Tab ) {
                    // Get the subfields from the tab
                    $sub_fields = $field->get_fields();
                }

                $fields[] = $field->export( $register );

                // Add the possibly stored subfields
                if ( ! empty( $sub_fields ) ) {
                    foreach ( $sub_fields as $sub_field ) {
                        $fields[] = $sub_field->export( $register );
                    }

                    unset( $sub_fields );
                }
            }

            // Remove keys, ACF requires the arrays to be numbered.
            $obj[ $this->fields_var ] = array_values( $fields );
        }

        // Convert the wrapper class array to a space-separated string.
        if ( isset( $obj['wrapper']['class'] ) && ! empty( $obj['wrapper']['class'] ) ) {
            $obj['wrapper']['class'] = implode( ' ', $obj['wrapper']['class'] );
        }
        else {
            $obj['wrapper']['class'] = '';
        }

        return $obj;
    }

    /**
     * Add field to group
     *
     * @throws \Geniem\ACF\Exception Throw error if given field is not valid.
     * @param \Geniem\ACF\Field $field A field to be added.
     * @param string            $order Whether the field is to be added first or last.
     * @return self
     */
    public function add_field( \Geniem\ACF\Field $field, $order = 'last' ) {
        // Add the field to the fields array.
        if ( $order === 'first' ) {
            $this->self->{ $this->fields_var } = [ $field->get_name() => $field ] + $this->self->{ $this->fields_var };
        }
        else {
            $this->self->{ $this->fields_var }[ $field->get_name() ] = $field;
        }

        return $this->self;
    }

    /**
     * Add a field to the group before a target field.
     *
     * @param \Geniem\ACF\Field $field  A field to be added.
     * @param [mixed]           $target A target field.
     * @return self
     */
    public function add_field_before( \Geniem\ACF\Field $field, $target ) {
        // Call the real function.
        return $this->add_field_location( $field, 'before', $target );
    }

    /**
     * Add a field to the group after a target field.
     *
     * @param \Geniem\ACF\Field $field  A field to be added.
     * @param [mixed]           $target A target field.
     * @return self
     */
    public function add_field_after( \Geniem\ACF\Field $field, $target ) {
        // Call the real function.
        return $this->add_field_location( $field, 'after', $target );
    }

    /**
     * A method for the two previous methods to use.
     *
     * @throws \Geniem\ACF\Exception Throw error if given target is not valid.
     *
     * @param  \Geniem\ACF\Field $field  A field to be added.
     * @param  [string]          $action Whether it's added before or after.
     * @param  [mixed]           $target A target field.
     * @return self
     */
    private function add_field_location( \Geniem\ACF\Field $field, $action, $target ) {
        // If given a field instance, replace the value with its name.
        if ( $target instanceof \ Geniem\ACF\Field ) {
            $target = $target->get_name();
        }

        // Check if the target field exists in the field group.
        if ( ! isset( $this->self->{ $this->fields_var }[ $target ] ) ) {
            throw new \Geniem\ACF\Exception( 'Geniem\ACF\Field\Group: add_field_'. $action .' can\'t find given target "'. $target .'"' );
        }

        // Make a copy of the fields array to work with.
        $fields = [];

        // Loop through the fields and populate the new array.
        foreach ( $this->self->{ $this->fields_var } as $name => $item ) {

            // If this's the spot, do the right thing.
            if ( $action === 'before' && $name === $target ) {
                $fields[ $field->get_name() ] = $field;
            }

            // Insert the original inhabitant.
            $fields[ $name ] = $item;

            // And if this's the spot, do the right thing here.
            if ( $action === 'after' && $name === $target ) {
                $fields[ $field->get_name() ] = $field;
            }
        }

        // Replace the original fields array with the new one.
        $this->self->{ $this->fields_var } = $fields;

        return $this->self;
    }

    /**
     * Remove field from sub fields
     *
     * @param  string $field_name Name of the field to remove.
     * @return self
     */
    public function remove_field( string $field_name ) {

        if ( isset( $this->self->{ $this->fields_var }[ $field_name ] ) ) {
            unset( $this->self->{ $this->fields_var }[ $field_name ] );
        }

        return $this->self;
    }

    /**
     * Set fields
     *
     * @param array $fields Fields to set.
     * @return self
     */
    public function set_fields( $fields ) {
        $this->self->{ $this->fields_var } = $fields;

        return $this->self;
    }

    /**
     * Get fields
     *
     * @return array
     */
    public function get_fields() {
        return $this->self->{ $this->fields_var };
    }

    /**
     * Get a field
     *
     * @param string $name Field name.
     * @return array
     */
    public function get_field( $name ) {
        return $this->self->{ $this->fields_var }[ $name ] ?? null;
    }

    /**
     * Remove all sub fields
     *
     * @return self
     */
    public function remove_fields() {
        unset( $this->self->{ $this->fields_var } );

        return $this->self;
    }

    /**
     * Clone method
     *
     * Forces the developer to give new key to cloned field.
     *
     * @param string $key  Field key.
     * @param string $name Field name (optional).
     * @return Geniem\ACF\Field
     */
    public function clone( $key, $name = null ) {
        $clone = clone $this->self;

        $clone->set_key( $key );

        if ( isset( $name ) ) {
            $clone->set_name( $name );
        }

        $clone->{ $this->fields_var } = array_map( function( $field ) use ( $key ) {
            return $field->clone( $key . '_' . $field->get_key() );
        }, $clone->{ $this->fields_var });

        $clone->update_self();

        return $clone;
    }
}
