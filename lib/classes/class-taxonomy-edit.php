<?php
if (!defined('ABSPATH')) exit;  // if direct access 


if (!class_exists('TaxonomyEdit')) {

    class TaxonomyEdit
    {

        public $data = array();

        public function __construct($args)
        {

            $this->data = &$args;
            add_action($this->get_taxonomy() . '_add_form_fields', array($this, 'add_form_fields'), 12);
            add_action($this->get_taxonomy() . '_edit_form_fields', array($this, 'edit_form_fields'), 12);
            add_action('edited_' . $this->get_taxonomy(), array($this, 'save_update_taxonomy'), 12);
            add_action('create_' . $this->get_taxonomy(), array($this, 'save_update_taxonomy'), 12);
        }

        public function save_update_taxonomy($term_id)
        {
            foreach ($this->get_panels() as $optionIndex => $option) :
                $option_value       = isset($_POST[$option['id']]) ? mage_array_strip($_POST[$option['id']]) : '';
                if (is_array($option_value)) {
                    $option_value   = serialize($option_value);
                }
                update_term_meta($term_id, $option['id'], $option_value);
            endforeach;
        }


        public function edit_form_fields($term)
        {

            $term_id = $term->term_id;
            foreach ($this->get_panels() as $optionIndex => $option) :  ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="<?php echo esc_attr($option['id']); ?>"><?php echo esc_html($option['title']); ?></label></th>
                    <td>
                        <?php

                        $this->field_generator($option, $term_id)
                        ?>
                    </td>
                </tr>
            <?php
            endforeach;
        }

        public function add_form_fields($term)
        {
            $term_id = '';
            ?>
            <?php foreach ($this->get_panels() as $optionIndex => $option) : ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="<?php echo esc_attr($option['id']); ?>"><?php echo esc_html($option['title']); ?></label></th>
                    <td>
                        <?php $this->field_generator($option, $term_id); ?>
                    </td>
                </tr>
            <?php
            endforeach;
        }

        public function field_generator($option, $term_id)
        {

            $id             = isset($option['id']) ? $option['id'] : "";
            $type           = isset($option['type']) ? $option['type'] : "";
            $details        = isset($option['details']) ? $option['details'] : "";

            if (empty($id)) return;
            $option['field_name']       = $id;
            $option_value               = get_term_meta($term_id,  $id, true);
            $option['value']            = is_serialized($option_value) ? unserialize($option_value) : $option_value;

            if (sizeof($option) > 0 && isset($option['type'])) {
                echo mep_field_generator($option['type'], $option);
                do_action("wp_theme_settings_field_$type", $option);
            }
            if (!empty($details)) echo "<p class='description'>$details</p>";
        }

        private function get_taxonomy()
        {
            if (isset($this->data['taxonomy'])) return $this->data['taxonomy'];
            else return "category";
        }

        private function get_panels()
        {
            if (isset($this->data['options'])) return $this->data['options'];
            else return array();
        }
    }
}
