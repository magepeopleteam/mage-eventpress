<?php
if ( ! defined('ABSPATH')) exit;  // if direct access 


/*Input fields
*  Text
*  Select
*  Checkbox
*  Checkbox Multi
*  Radio
*  Textarea
*  Number
*  Hidden
*  Range
*  Color
*  Email
*  URL
*  Tel
*  Search
*  Month
*  Week
*  Date
*  Time
*  Submit
 *
 *
*  Text multi
*  Select multi
*  Select2
*  Range with input
*  Color picker
*  Datepicker
*  Media
*  Media Gallery
*  Switcher
*  Switch
*  Switch multi
*  Switch image
*  Dimensions (width, height, custom)
*  WP Editor
*  Code Editor
*  Link Color
*  Repeatable
*  Icon
*  Icon multi
*  Date format
*  Time format
*  FAQ
*  Grid
*  Custom_html
*  Color palette
*  Color palette multi
 * Color set
*  User select
*  Color picker multi
*  Google reCaptcha
*  Nonce
*  Border
*  Margin
*  Padding
*  Google Map
*  Image Select
 *
 *
 * Background
 *
 * Typography
 * Spinner


*/






if( ! class_exists( 'FormFieldsGenerator' ) ) {

    class FormFieldsGenerator {




        public function field_post_objects( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : array();

            $values 	    = !empty( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if(!empty($values)):

                foreach ($values as $value):
                    $values_sort[$value] = $value;
                endforeach;
                $args = array_replace($values_sort, $args);
            endif;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';
            endif;
            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field';
                    ?> field-wrapper field-post-objects-wrapper field-post-objects-wrapper-<?php echo esc_attr($field_id); ?>">
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $argsKey=>$arg):
                            ?>
                            <div class="item">
                                <?php if($sortable):?>
                                    <span class="ppof-button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>
                                <label>
                                    <input type="checkbox" <?php if(in_array($argsKey,$values)) echo 'checked';?>  value="<?php
                                    echo esc_attr($argsKey); ?>" name="<?php echo esc_attr($field_name); ?>[]">
                                    <span><?php echo esc_attr($arg); ?></span>
                                </label>
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>

            </div>
            <?php
            return ob_get_clean();
        }

        public function field_switcher( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $checked = !empty($value) ? 'checked':'';
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switcher-wrapper
            field-switcher-wrapper-<?php echo esc_attr($id); ?>">
                <label class="switcher <?php echo esc_attr($checked); ?>">
                    <input type="checkbox" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>"
                           name="<?php echo esc_attr($field_name); ?>" <?php echo esc_attr($checked); ?>>
                    <span class="layer"></span>
                    <span class="mpev-slider"></span>
                    <?php
                    if(!empty($args))
                    foreach ($args as $index=>$arg):
                        ?>
                        <span  unselectable="on" class="switcher-text <?php echo esc_attr($index); ?>"><?php echo esc_html($arg);
                        ?></span>
                    <?php
                    endforeach;
                    ?>
                </label>
                <div class="error-mgs"></div>
            </div>




            <?php
            return ob_get_clean();
        }




        public function field_google_map( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $preview 	        = isset( $option['preview'] ) ? $option['preview'] : false;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            $lat  = isset($values['lat']) ? $values['lat'] : '';
            $lng   = isset($values['lng']) ? $values['lng'] :'';
            $zoom  = isset($values['zoom']) ? $values['zoom'] : '';
            $title  = isset($values['title']) ? $values['title'] : '';
            $apikey  = isset($values['apikey']) ? $values['apikey'] : '';


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>

                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                        id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-map-wrapper
                field-google-map-wrapper-<?php echo esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$name):
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='text' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>]' value='<?php
                                    echo esc_attr($values[$index]); ?>' /></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

                <?php
                if($preview):
                    ?>
                    <div id="map-<?php echo esc_attr($field_id); ?>"></div>
                    <script>
                        function initMap() {
                            var myLatLng = {lat: <?php echo esc_html($lat); ?>, lng: <?php echo esc_html($lng); ?>};
                            var map = new google.maps.Map(document.getElementById('map-<?php echo esc_html($field_id); ?>'), {
                                zoom: <?php echo esc_html($zoom); ?>,
                                center: myLatLng
                            });
                            var marker = new google.maps.Marker({
                                position: myLatLng,
                                map: map,
                                title: '<?php echo esc_html($title); ?>'
                            });
                        }
                    </script>
                    <script async defer
                            src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_html($apikey); ?>&callback=initMap">
                    </script>
                    <?php
                endif;
            endif;
            return ob_get_clean();
        }



        public function field_border( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            $width  = $values['width'];
            $unit   = $values['unit'];
            $style  = $values['style'];
            $color  = $values['color'];



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-border-wrapper
            field-border-wrapper-<?php echo esc_attr($id); ?>">
                <div class="item-list">
                        <div class="item">
                            <span class="field-title">Width</span>
                            <span class="input-wrapper">
                                <input type='number' name='<?php echo esc_attr($field_name);?>[width]' value='<?php  echo esc_attr($width); ?>' />
                            </span>
                            <select name="<?php echo esc_attr($field_name);?>[unit]">
                                <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                            </select>
                        </div>
                        <div class="item">
                            <span class="field-title">Style</span>
                            <select name="<?php echo esc_attr($field_name);?>[style]">
                                <option <?php if($style == 'dotted') echo 'selected'; ?> value="dotted">dotted</option>
                                <option <?php if($style == 'dashed') echo 'selected'; ?> value="dashed">dashed</option>
                                <option <?php if($style == 'solid') echo 'selected'; ?> value="solid">solid</option>
                                <option <?php if($style == 'double') echo 'selected'; ?> value="double">double</option>
                                <option <?php if($style == 'groove') echo 'selected'; ?> value="groove">groove</option>
                                <option <?php if($style == 'ridge') echo 'selected'; ?> value="ridge">ridge</option>
                                <option <?php if($style == 'inset') echo 'selected'; ?> value="inset">inset</option>
                                <option <?php if($style == 'outset') echo 'selected'; ?> value="outset">outset</option>
                                <option <?php if($style == 'none') echo 'selected'; ?> value="none">none</option>
                            </select>
                        </div>
                    <div class="item">
                        <span class="field-title">Color</span>
                        <span class="input-wrapper"><input class="colorpicker" type='text' name='<?php echo esc_attr($field_name);
                        ?>[color]' value='<?php echo esc_attr($color); ?>' /></span>
                    </div>
                </div>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }



        public function field_dimensions( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper
                field-margin-wrapper-<?php echo esc_attr($id); ?>">
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper"><input type='number' name='<?php echo esc_attr($field_name);?>[<?php
                                    echo esc_attr($index); ?>][val]' value='<?php
                                    echo esc_attr($values[$index]['val']); ?>' /></span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

            <?php
            endif;
            return ob_get_clean();
        }



        public function field_padding( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-padding-wrapper
                field-padding-wrapper-<?php echo esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_html($name); ?></span>
                                <span class="input-wrapper">
                                    <input type='number' name='<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][val]' value='<?php echo esc_attr($values[$index]['val']); ?>' />
                                </span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>

                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-padding-wrapper-<?php echo esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-padding-wrapper-<?php echo esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-padding-wrapper-<?php echo esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-padding-wrapper-<?php echo esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-padding-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>
            <?php
            endif;
            return ob_get_clean();
        }



        public function field_margin( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            if(!empty($args)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-margin-wrapper field-margin-wrapper-<?php echo esc_attr($id); ?>">
                    <label><input type="checkbox" class="change-together">Apply for all</label>
                    <div class="item-list">
                        <?php
                        foreach ($args as $index=>$arg):
                            $name = $arg['name'];
                            $unit = $values[$index]['unit'];
                            ?>
                            <div class="item">
                                <span class="field-title"><?php echo esc_attr($name); ?></span>
                                <span class="input-wrapper">
                                    <input class="<?php echo esc_attr($index); ?>" type='number' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][val]' value='<?php echo esc_attr($values[$index]['val']); ?>' />
                                </span>
                                <select name="<?php echo esc_attr($field_name);?>[<?php echo esc_attr($index); ?>][unit]">
                                    <option <?php if($unit == 'px') echo 'selected'; ?> value="px">px</option>
                                    <option <?php if($unit == '%') echo 'selected'; ?> value="%">%</option>
                                    <option <?php if($unit == 'em') echo 'selected'; ?> value="em">em</option>
                                    <option <?php if($unit == 'cm') echo 'selected'; ?> value="cm">cm</option>
                                    <option <?php if($unit == 'mm') echo 'selected'; ?> value="mm">mm</option>
                                    <option <?php if($unit == 'in') echo 'selected'; ?> value="in">in</option>
                                    <option <?php if($unit == 'pt') echo 'selected'; ?> value="pt">pt</option>
                                    <option <?php if($unit == 'pc') echo 'selected'; ?> value="pc">pc</option>
                                    <option <?php if($unit == 'ex') echo 'selected'; ?> value="ex">ex</option>
                                </select>
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('keyup change', '.field-margin-wrapper-<?php echo esc_attr($id); ?>  input[type="number"]',
                            function() {
                                is_checked = jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> .change-together').attr('checked');
                                if(is_checked == 'checked'){
                                    val = jQuery(this).val();
                                    i = 0;
                                    $('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                        if(i > 0){
                                            jQuery(this).val(val);
                                        }
                                        i++;
                                    });
                                }
                            })
                        jQuery(document).on('click', '.field-margin-wrapper-<?php echo esc_attr($id); ?> .change-together', function() {
                            is_checked = this.checked;
                            if(is_checked){
                                i = 0;
                                $('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').each(function( index ) {
                                    if(i > 0){
                                        jQuery(this).attr('readonly','readonly');
                                    }
                                    i++;
                                });
                                i = 0;
                                $('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').each(function( index ) {
                                    if(i > 0){
                                        //jQuery(this).attr('disabled','disabled');
                                    }
                                    i++;
                                });
                            }else{
                                jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> input[type="number"]').removeAttr('readonly');
                                //jQuery('.field-margin-wrapper-<?php echo esc_attr($id); ?> select').removeAttr('disabled');
                            }
                        })
                    })
                </script>

            <?php
            endif;
            return ob_get_clean();
        }



        public function field_google_recaptcha( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $secret_key 	= isset( $option['secret_key'] ) ? $option['secret_key'] : "";
            $site_key 	    = isset( $option['site_key'] ) ? $option['site_key'] : "";
            $version 	    = isset( $option['version'] ) ? $option['version'] : "";
            $action_name 	= isset( $option['action_name'] ) ? $option['action_name'] : "action_name";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-google-recaptcha-wrapper
            field-google-recaptcha-wrapper-<?php echo esc_attr($id);
            ?>">
                <?php if($version == 'v2'):?>
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    <script src='https://www.google.com/recaptcha/api.js'></script>
            <?php elseif($version == 'v3'):?>
                    <script src='https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr($site_key); ?>'></script>
                    <script>
                        grecaptcha.ready(function() {
                            grecaptcha.execute('<?php echo esc_attr($site_key); ?>', {action: '<?php echo esc_attr($action_name); ?>'})
                                .then(function(token) {
// Verify the token on the server.
                                });
                        });
                    </script>

                <?php endif;?>
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }


        public function field_img_select( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-img-select-wrapper
            field-img-select-wrapper-<?php echo esc_attr($id); ?>">
                <div class="img-list">
                    <?php
                    foreach( $args as $key => $arg ):
                        $checked = ( $arg == $value ) ? "checked" : "";
                        ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img data-id="<?php echo esc_attr($id); ?>" src="<?php echo esc_attr($arg); ?>"> </span></label><?php

                    endforeach;
                    ?>
                </div>
                <div class="img-val">
                    <input type="text" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();

        }





        public function field_submit( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-submit-wrapper
            field-submit-wrapper-<?php echo esc_attr($id); ?>">
                <input type='submit' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_nonce( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $action_name 	    = isset( $option['action_name'] ) ? $option['action_name'] : "";

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-nonce-wrapper
            field-nonce-wrapper-<?php echo esc_attr($id); ?>">
                <?php wp_nonce_field( $action_name, $field_name ); ?>
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }



        public function field_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-wrapper
            field-color-wrapper-<?php echo esc_attr($id); ?>">
                <input type='color' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }




        public function field_email( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-email-wrapper
            field-email-wrapper-<?php echo esc_attr($id); ?>">
                <input type='email' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }


        public function field_password( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $password_meter = isset( $option['password_meter'] ) ? $option['password_meter'] : true;
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-password-wrapper
            field-password-wrapper-<?php echo esc_attr($id); ?>">
                <input type='password' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <?php if($password_meter): ?>
                <div class="scorePassword"></div>
                <div class="scoreText"></div>
                <?php endif; ?>
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }

        public function field_search( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-search-wrapper
            field-search-wrapper-<?php echo esc_attr($id); ?>">
                <input type='search' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }

        public function field_month( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-month-wrapper
            field-month-wrapper-<?php echo esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }

        public function field_date( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-wrapper
            field-date-wrapper-<?php echo esc_attr($id); ?>">
                <input type='date' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_url( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-url-wrapper field-url-wrapper-<?php echo esc_attr($id); ?>">
                <input type='url' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_time( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-wrapper
            field-time-wrapper-<?php echo esc_attr($id); ?>">
                <input type='time' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_tel( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-tel-wrapper field-tel-wrapper-<?php
            echo esc_attr($id); ?>">
                <input type='tel' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_text( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id))  return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();




            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-wrapper
         field-text-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'
                       placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>


            <?php

            return ob_get_clean();
        }


        public function field_hidden( $option ){




            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>


            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-hidden-wrapper
            field-hidden-wrapper-<?php echo esc_attr($id); ?>">
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php
                echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();
        }




        public function field_text_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;

            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-trash"></i>';
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $values 	    = isset( $option['value'] ) ? $option['value'] : array();
            $values         = !empty($values) ? $values : $default;
            $limit 	        = !empty( $option['limit'] ) ? $option['limit'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-text-multi-wrapper
            field-text-multi-wrapper-<?php echo esc_attr($field_id); ?>">
                <span class="ppof-button add-item"><?php echo __('Add','mage-eventpress'); ?></span>
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($field_id); ?>">
                    <?php
                    if(!empty($values)):
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php
                                echo esc_attr($placeholder); ?>' value="<?php echo esc_attr($value); ?>" />

                                <span class="ppof-button clone"><i class="far fa-clone"></i></span>

                                <?php if($sortable):?>
                                <span class="ppof-button sort"><i class="fas fa-arrows-alt"></i></span>
                                <?php endif; ?>

                                <span class="ppof-button remove" onclick="jQuery(this).parent().remove()"><?php echo ($remove_text); ?></span>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="item">
                            <input type='text' name='<?php echo esc_attr($field_name); ?>[]'  placeholder='<?php echo
                            esc_attr($placeholder); ?>'
                                   value='' /><span class="button remove" onclick="jQuery(this).parent().remove()
"><?php echo ($remove_text); ?></span>
                            <?php if($sortable):?>
                                <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                            <?php endif; ?>
                            <span class="button clone"><i class="far fa-clone"></i></span>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
                <script>
                    jQuery(document).ready(function($) {
                        jQuery(document).on('click', '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .clone',function(){


                            <?php
                            if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list .item" ).size();
                            if(limit > node_count){
                                $( this ).parent().clone().appendTo('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                            }else{
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }
                            <?php
                            else:
                            ?>
                            $( this ).parent().clone().appendTo('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                            <?php
                            endif;
                            ?>

                            //$( this ).parent().appendTo( '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list' );


                        })
                    jQuery(document).on('click', '.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .add-item',function(){


                        html_<?php echo esc_attr($id); ?> = '<div class="item">';
                        html_<?php echo esc_attr($id); ?> += '<input type="text" name="<?php echo esc_attr($field_name); ?>[]" placeholder="<?php
                            echo esc_attr($placeholder); ?>" />';
                        html_<?php echo esc_attr($id); ?> += '<span class="button remove" onclick="jQuery(this).parent().remove()' +
                            '"><?php echo ($remove_text); ?></span>';
                        html_<?php echo esc_attr($id); ?> += '<span class="button clone"><i class="far fa-clone"></i></span>';
                        <?php if($sortable):?>
                        html_<?php echo esc_attr($id); ?> += ' <span class="button sort" ><i class="fas fa-arrows-alt"></i></span>';
                        <?php endif; ?>
                        html_<?php echo esc_attr($id); ?> += '</div>';


                        <?php
                        if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list .item" ).size();
                            if(limit > node_count){
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list').append(html_<?php echo esc_attr($id); ?>);
                            }else{
                                jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }
                            <?php
                        else:
                            ?>
                            jQuery('.field-text-multi-wrapper-<?php echo esc_attr($id); ?> .field-list').append(html_<?php echo esc_attr($id); ?>);
                            <?php
                        endif;
                        ?>




                    })

                })
                </script>

            </div>
            <?php
            return ob_get_clean();

        }



        public function field_textarea( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $visible 	    = isset( $option['visible'] ) ? $option['visible'] : "";
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $_value         = !empty($value) ? $value : $default;
            $__value        = str_replace('<br />', PHP_EOL, html_entity_decode($_value));
                  
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-textarea-wrapper field-textarea-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo mep_esc_html($__value); ?></textarea>
                <div class="error-mgs"></div>
            </div>



            <?php
            return ob_get_clean();
        }


        public function field_code( $option ){

            $id             = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $args	        = isset( $option['args'] ) ? $option['args'] : array(
                'lineNumbers'	=> true,
                'mode'	=> "javascript",
            );


            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>"  class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper  field-code-wrapper
            field-code-wrapper-<?php echo esc_attr($field_id); ?>">
                <textarea name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' cols='40' rows='5' placeholder='<?php echo esc_attr($placeholder); ?>'><?php echo esc_attr($value); ?></textarea>
                <div class="error-mgs"></div>
            </div>
            <script>
                var editor = CodeMirror.fromTextArea(document.getElementById("<?php echo esc_attr($field_id); ?>"), {
                    <?php
                    foreach ($args as $argkey=>$arg):
                        echo esc_html($argkey).':'.esc_html($arg).',';
                    endforeach;
                    ?>
                });
            </script>

            <?php
            return ob_get_clean();
        }

        public function field_checkbox( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = (  $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }

        public function field_checkbox_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : "";

            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name.'[]' : $id.'[]';



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-checkbox-wrapper
            field-checkbox-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>'><input class="<?php echo esc_attr($field_id); ?>" name='<?php
                        echo esc_attr($field_name); ?>' type='checkbox' id='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>' value='<?php
                        echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_radio( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : array();
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-radio-wrapper
            field-radio-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label for='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($field_id).'-'.esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><?php echo esc_html($argName); ?></label><br>
                <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_select( $option ){

            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;
            $limit 	    = !empty( $option['limit'] ) ? $option['limit'] : '';
            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select-wrapper
            field-select-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                        <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;

                foreach( $args as $key => $argName ):
                    if( $multiple ) $selected = is_array( $value ) && in_array( $key, $value ) ? "selected" : "";
                    else $selected = ($value == $key) ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_html($argName); ?></option>
                    <?php
                endforeach;
                ?>
                </select>


                <div class="error-mgs"></div>

            </div>
            <script>
                jQuery(document).ready(function($) {

                    <?php
                    if($limit > 0):
                        ?>
                        jQuery(document).on('change', '.field-select-wrapper-<?php echo esc_attr($id); ?> select', function() {

                            last_value = $('.field-select-wrapper-<?php echo esc_attr($id); ?> select :selected').last().val();

                            var node_count = $( ".field-select-wrapper-<?php echo esc_attr($id); ?> select :selected" ).size();

                            console.log(last_value);

                            var limit = <?php  echo esc_attr($limit); ?>;
                            //var node_count = $(".field-select-wrapper-<?php echo esc_attr($id); ?> select :selected").length;
                            //var node_count = $( ".field-select-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                            //console.log(node_count);
                            if(limit >= node_count){

                                //jQuery('.<?php echo 'field-select-wrapper-'.$id; ?> .field-list').append(html);
                            }else{
                                $(".field-select-wrapper-<?php echo esc_attr($id); ?> select option[value='"+last_value+"']").prop("selected", false);
                                jQuery('.field-select-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can select max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                        })
                        <?php

                    endif;
                    ?>





                })






            </script>
            <?php
            return ob_get_clean();
        }


        public function field_range( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-wrapper
            field-range-wrapper-<?php echo esc_attr($id); ?>">
                <input type='range' min='<?php echo esc_attr($min); ?>' max='<?php echo esc_attr($max); ?>' step='<?php echo esc_attr($args['step']); ?>' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_range_input( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $min            = isset( $args['min'] ) ? $args['min'] : 0;
            $max            = isset( $args['max'] ) ? $args['max'] : 100;
            $step           = isset( $args['step'] ) ? $args['step'] : 1;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-range-input-wrapper
            field-range-input-wrapper-<?php echo esc_attr($id); ?>">
                <input type="number" class="range-val" name='<?php echo esc_attr($field_name); ?>' value="<?php echo esc_attr($value); ?>">
                <input type='range' class='range-hndle' id="<?php echo esc_attr($field_id); ?>" min='<?php echo esc_attr($args['min']); ?>' max='<?php echo esc_attr($args['max']); ?>' step='<?php echo esc_attr($args['step']); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_switch( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-wrapper
            field-switch-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_html($argName); ?></span></label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>


            <?php
            return ob_get_clean();
        }



        public function field_switch_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value			= isset( $option['value'] ) ? $option['value'] : array();
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-multi-wrapper
            field-switch-multi-wrapper-<?php echo
            $id; ?>">
                <?php
                foreach( $args as $key => $argName ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($field_id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><?php echo esc_html($argName); ?></span></label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_switch_img( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $width			= isset( $option['width'] ) ? $option['width'] : "";
            $height			= isset( $option['height'] ) ? $option['height'] : "";
            $default 		= isset( $option['default'] ) ? $option['default'] : '';
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-switch-img-wrapper
            field-switch-img-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $args as $key => $arg ):
                    $src = isset( $arg['src'] ) ? $arg['src'] : "";

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span class="sw-button"><img src="<?php echo esc_attr($src); ?>"> </span></label><?php

                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_time_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-time-format-wrapper
            field-time-format-wrapper-<?php echo esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo esc_attr($item); ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>">
                                    <span class="name"><?php echo date($item); ?></span></label>
                                <span class="format"><code><?php echo esc_attr($item); ?></code></span>
                            </div>
                        <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo date($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }






        public function field_date_format( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 	= isset( $option['default'] ) ? $option['default'] : "";
            $args 	= isset( $option['args'] ) ? $option['args'] : "";

            $value 	= isset( $option['value'] ) ? $option['value'] : "";
            $value 	 		= !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-date-format-wrapper
            field-date-format-wrapper-<?php echo esc_attr($id); ?>">
                <div class="format-list">
                    <?php
                    if(!empty($args)):
                        foreach ($args as $item):
                            $checked = ($item == $value) ? 'checked':false;
                            ?>
                            <div class="format" datavalue="<?php echo esc_attr($item); ?>">
                                <label><input type="radio" <?php echo esc_attr($checked); ?> name="preset_<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($item); ?>"><span class="name"><?php echo date($item); ?></span></label>
                                <span class="format"><code><?php echo esc_html($item); ?></code></span>
                            </div>
                            <?php
                        endforeach;
                        ?>
                        <div class="format-value">
                            <span class="format"><input value="<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($field_name); ?>"></span>
                            <div class="">Preview: <?php echo date($value); ?></div>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_datepicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $date_format	= isset( $option['date_format'] ) ? $option['date_format'] : "dd-mm-yy";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ?$value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-datepicker-wrapper
            field-datepicker-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $('#<?php echo esc_attr($field_id); ?>').datepicker({dateFormat : '<?php echo esc_attr($date_format); ?>'})});
            </script>

            <?php
            return ob_get_clean();
        }






        public function field_colorpicker( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-wrapper
            field-colorpicker-wrapper-<?php echo esc_attr($id); ?>">
                <input type='text'  name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($) { $('#<?php echo esc_attr($field_id); ?>').wpColorPicker();});</script>

            <?php
            return ob_get_clean();
        }


        public function field_colorpicker_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : "";
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : "X";
            $default 	= isset( $option['default'] ) ? $option['default'] : array();

            $values = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            if(!empty($values)):
                ?>
                <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-colorpicker-multi-wrapper
                field-colorpicker-multi-wrapper-<?php echo esc_attr($id);
                ?>">
                    <div class="ppof-button add"><?php echo __('Add','mage-eventpress'); ?></div>
                    <div class="item-list">
                        <?php
                        foreach ($values as $value):
                            ?>
                            <div class="item">
                                <span class="ppof-button remove"><?php echo esc_html($remove_text); ?></span>
                                <input type='text' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="error-mgs"></div>
                </div>
                <?php
            endif;
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list .remove', function(){
                        jQuery(this).parent().remove();
                    })
                    jQuery(document).on('click', '.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .add', function() {
                        html='<div class="item">';
                        html+='<span class="button remove"><?php echo esc_html($remove_text); ?></span> <input type="text"  name="<?php echo esc_attr($field_name); ?>[]" value="" />';
                        html+='</div>';


                        <?php
                        if(!empty($limit)):
                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list .item" ).size();
                        if(limit > node_count){

                            $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .item-list').append(html);
                            $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> input').wpColorPicker();


                        }else{
                            jQuery('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        endif;
                        ?>





                    })
                    $('.field-colorpicker-multi-wrapper-<?php echo esc_attr($id); ?> input').wpColorPicker();
                });
            </script>

            <?php

            return ob_get_clean();
        }




        public function field_link_color( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $args 	        = isset( $option['args'] ) ? $option['args'] : array('link'	=> '#1B2A41','hover' => '#3F3244','active' => '#60495A','visited' => '#7D8CA3' );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-link-color-wrapper
            field-link-color-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if(!empty($values) && is_array($values)):
                    foreach ($args as $argindex=>$value):
                        ?>
                        <div>
                            <div class="item"><span class="title">a:<?php echo esc_html($argindex); ?> Color</span><div class="colorpicker"><input type='text' class='<?php echo esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($argindex); ?>]'   value='<?php echo esc_attr($values[$argindex]); ?>' /></div></div>
                        </div>
                        <?php
                    endforeach;
                else:
                    foreach ($args as $argindex=>$value):
                        ?>
                        <div>
                            <div class="item"><span class="title">a:<?php echo esc_html($argindex); ?> Color</span><div class="colorpicker"><input type='text' class='<?php echo esc_attr($id); ?>' name='<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($argindex); ?>]'   value='<?php echo esc_attr($value); ?>' /></div></div>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($) { $('.<?php echo esc_attr($id); ?>').wpColorPicker();});</script>

            <?php
            return ob_get_clean();
        }






        public function field_user( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-user-multi-wrapper
            field-user-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class="users-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $user_id):
                            $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));

                            ?><div class="item" title="click to remove"><img src="<?php echo esc_attr($get_avatar_url); ?>" /><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($user_id); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="user-list">
                    <div class="ppof-button select-user" ><?php echo __('Choose User','mage-eventpress');?></div>
                    <div class="search-user" ><input class="" type="text" placeholder="<?php echo __('Start typing...','mage-eventpress');?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $user_id=>$iconTitle):
                                $user_data = get_user_by('ID',$user_id);
                                $get_avatar_url = get_avatar_url($user_id,array('size'=>'60'));
                                ?>
                                <li title="<?php echo esc_attr($user_data->display_name); ?>(#<?php echo esc_attr($user_id); ?>)"
                                    userSrc="<?php echo
                                $get_avatar_url; ?>"
                                    iconData="<?php echo esc_attr($user_id); ?>"><img src="<?php echo esc_attr($get_avatar_url); ?>" />
                                </li>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>


            <script>
                jQuery(document).ready(function($){
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .users-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .select-user', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .search-user input', function(){
                        text = jQuery(this).val();
                        $('.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .user-list li').each(function( index ) {
                            //console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .user-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        userSrc = jQuery(this).attr('userSrc');
                        html = '';
                        html = '<div class="item" title="click to remove"><img src="'+userSrc+'" /><input type="hidden" ' +
                        'name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';
                        jQuery('.field-user-multi-wrapper-<?php echo esc_attr($id); ?> .users-wrapper').append(html);
                    })
                })
            </script>

            <?php
            return ob_get_clean();
        }



        public function field_icon( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;

            $icons		    = is_array( $args ) ? $args : $this->args_from_string( $args );

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-wrapper
            field-icon-wrapper-<?php echo esc_attr($id); ?>">
                <div class="icon-wrapper" >
                    <span><i class="<?php echo esc_attr($value); ?>"></i></span>
                    <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <div class="icon-list">
                    <div class="ppof-button select-icon" ><?php echo __('Choose Icon','mage-eventpress'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo __('Start typing...','mage-eventpress'); ?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?>
                                <li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }



        public function field_icon_multi( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $args 			= isset( $option['args'] ) ? $option['args'] : array();
            $default 	    = isset( $option['default'] ) ? $option['default'] : array();
            $icons		    = is_array( $args ) ? $args :  $this->args_from_string( $args );

            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : "";
            $value 	        = isset( $option['value'] ) ? $option['value'] : "";
            $values         = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-icon-multi-wrapper
            field-icon-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class="icons-wrapper" >
                    <?php if(!empty($values)):
                        foreach ($values as $value):
                            ?><div class="item" title="click to remove"><span><i class="<?php echo esc_attr($value); ?>"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="<?php echo esc_attr($value); ?>"></div><?php
                        endforeach;
                    endif; ?>
                </div>
                <div class="icon-list">
                    <div class="ppof-button select-icon" ><?php echo __('Choose Icon','mage-eventpress'); ?></div>
                    <div class="search-icon" ><input class="" type="text" placeholder="<?php echo __('Start typing...','mage-eventpress'); ?>"></div>
                    <ul>
                        <?php
                        if(!empty($icons)):
                            foreach ($icons as $iconindex=>$iconTitle):
                                ?><li title="<?php echo esc_attr($iconTitle); ?>" iconData="<?php echo esc_attr($iconindex); ?>"><i class="<?php echo esc_attr($iconindex); ?>"></i></li><?php
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
                <div class="error-mgs"></div>
            </div>


            <script>
                jQuery(document).ready(function($){


                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper .item', function(){
                        jQuery(this).remove();
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .select-icon', function(){
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                    jQuery(document).on('keyup', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .search-icon input', function(){
                        text = jQuery(this).val();
                        $('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icon-list li').each(function( index ) {
                            console.log( index + ": " + $( this ).attr('title') );
                            title = $( this ).attr('title');
                            n = title.indexOf(text);
                            if(n<0){
                                $( this ).hide();
                            }else{
                                $( this ).show();
                            }
                        });
                    })
                    jQuery(document).on('click', '.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icon-list li', function(){
                        iconData = jQuery(this).attr('iconData');
                        html = '<div class="item" title="click to remove"><span><i class="'+iconData+'"></i></span><input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+iconData+'"></div>';


                        <?php
                        if(!empty($limit)):
                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper .item" ).size();
                        if(limit > node_count){

                            jQuery('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .icons-wrapper').append(html);


                        }else{
                            jQuery('.field-icon-multi-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        endif;
                        ?>




                    })


                })
            </script>

            <?php
            return ob_get_clean();
        }









        public function field_number( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $default 			= isset( $option['default'] ) ? $option['default'] : "";
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $value 			= isset( $option['value'] ) ? $option['value'] : "";
            $value = !empty($value) ? $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
             <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-number-wrapper
             field-number-wrapper-<?php echo esc_attr($id); ?>">
                <input type='number' class='' name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>' placeholder='<?php echo esc_attr($placeholder); ?>' value='<?php echo esc_attr($value); ?>' />
                 <div class="error-mgs"></div>
             </div>

            <?php
            return ob_get_clean();
        }



        public function field_wp_editor( $option ){

            $id = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder    = isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $default        = isset( $option['default'] ) ? $option['default'] : "";
            $editor_settings= isset( $option['editor_settings'] ) ? $option['editor_settings'] : array('textarea_name'=>$field_name);
            $value 			= isset( $option['value'] ) ? $option['value'] : "";
            $value          = !empty($value) ? $value : $default;
            $field_id       = $id;
            
            
            // $value = preg_replace('#^<\/p>|<p>&nbsp;$#', '', $value);
// $value = preg_replace('/^[ \t]*[\r\n]+/m', '', $value);

            
            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-wp_editor-wrapper
            field-wp_editor-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                wp_editor( htmlspecialchars_decode($value), $id, $settings = $editor_settings);
                ?>
                <div class="error-mgs"></div>
            </div>
            <?php
            return ob_get_clean();
        }




        public function field_select2( $option ){

            $id 	    = isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args 	        = isset( $option['args'] ) ? $option['args'] : "";
            $args	    = is_array( $args ) ? $args : $this->args_from_string( $args );
            $default    = isset( $option['default'] ) ? $option['default'] : "";
            $multiple 	= isset( $option['multiple'] ) ? $option['multiple'] : false;

            $value		= isset( $option['value'] ) ? $option['value'] : '';
            $value      = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;

            if($multiple):
                $value = !empty($value) ? $value : array();
            endif;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-select2-wrapper
            field-select2-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                if($multiple):
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>[]' id='<?php echo esc_attr($field_id); ?>' multiple>
                    <?php
                else:
                    ?>
                    <select name='<?php echo esc_attr($field_name); ?>' id='<?php echo esc_attr($field_id); ?>'>
                    <?php
                endif;
                foreach( $args as $key => $name ):

                    if( $multiple ) $selected = in_array( $key, $value ) ? "selected" : "";
                    else $selected = $value == $key ? "selected" : "";
                    ?>
                    <option <?php echo esc_attr($selected); ?> value='<?php echo esc_attr($key); ?>'><?php echo esc_attr($name); ?></option>
                    <?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            </select>


            <?php
            return ob_get_clean();

        }


        public function field_option_group( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $options			= isset( $option['options'] ) ? $option['options'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>

            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-option-group-tabs-wrapper
            field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?>">

                        <?php
                        if(!empty($options)):
                            ?>
                            <table class="form-table">
                                <tbody>

                                <?php

                                foreach ($options as $key =>$option):

                                    $option_id = isset($option['id']) ? $option['id'] : '';
                                    $option_title = isset($option['title']) ? $option['title'] : '';


                                    $option['field_name'] = $field_name.'['.$option_id.']';
                                    $option['value'] = isset($values[$option_id]) ? $values[$option_id] : '';


                                    ?>
                                    <tr>
                                        <th scope="row"><?php echo esc_html($option_title); ?></th>
                                        <td>
                                            <?php                                           
                                            if (sizeof($option) > 0 && isset($option['type'])) {
                                                echo mep_field_generator($option['type'], $option);
                                                do_action("wp_theme_settings_field_$type", $option);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php




                                endforeach;

                                ?>


                                </tbody>
                            </table>
                        <?php

                        endif;
                        ?>


                    <?php

                ?>

                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function field_option_group_tabs( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-option-group-tabs-wrapper
            field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?>">

                <ul class="tab-navs">
                    <?php
                    $i = 1;
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        ?>
                        <li index="<?php echo esc_attr($i); ?>" class="<?php if($i == 1) echo 'active'; ?>"><?php echo esc_html($title); ?></li>
                        <?php
                        $i++;
                    endforeach;
                    ?>
                </ul>


                    <?php
                    $i = 1;
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $options = $value['options'];
                        ?>
                        <div class="tab-content tab-content-<?php echo esc_attr($i); ?> <?php if($i == 1) echo 'active'; ?>">


                                <?php
                                if(!empty($options)):
                                    ?>
                                    <table class="form-table">
                                        <tbody>

                                        <?php

                                        foreach ($options as $option):

                                            $option_id = isset($option['id']) ? $option['id'] : '';
                                            $option_title = isset($option['title']) ? $option['title'] : '';

                                            $option['field_name'] = $field_name.'['.$key.']['.$option_id.']';
                                            $option['value'] = isset($values[$key][$option_id]) ? $values[$key][$option_id] : '';


                                            ?>
                                            <tr>
                                                <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                <td>
                                                    <?php
                                                        if (sizeof($option) > 0 && isset($option['type'])) {
                                                            echo mep_field_generator($option['type'], $option);
                                                            do_action("wp_theme_settings_field_$type", $option);
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php




                                        endforeach;

                                        ?>


                                        </tbody>
                                    </table>
                                <?php

                                endif;
                                ?>

                        </div>
                    <?php
                        $i++;
                    endforeach;
                    ?>

                <div class="error-mgs"></div>
            </div>
            <script>

                jQuery(document).on('click', '.field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-navs li', function() {

                    index = $(this).attr('index');

                    jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-navs li").removeClass('active');
                    jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-content").removeClass('active');
                    if(jQuery(this).hasClass('active')){

                    }else{
                        jQuery(this).addClass('active');
                        jQuery(".field-option-group-tabs-wrapper-<?php echo esc_attr($id); ?> .tab-content-"+index).addClass('active');
                    }



                })


            </script>
            <?php
            return ob_get_clean();
        }


        public function field_option_group_accordion( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $FormFieldsGenerator = new FormFieldsGenerator();

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;




            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php echo esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php echo esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title      = $value['title'];
                        $link       = $value['link'];
                        $options    = $value['options'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo esc_html($title); ?></div>
                            <div class="faq-content">
                                <?php
                                if(!empty($options)):
                                    ?>
                                    <table class="form-table">
                                        <tbody>
                                        <?php
                                        foreach ($options as $option):
                                            $option['field_name'] = $field_name.'['.$key.']['.$option['id'].']';
                                            $option['value'] = $values[$key][$option['id']];
                                                ?>
                                                <tr>
                                                    <th scope="row"><?php echo esc_html($option['title']); ?></th>
                                                    <td>
                                                        <?php
                                                            if (sizeof($option) > 0 && isset($option['type'])) {
                                                                echo mep_field_generator($option['type'], $option);
                                                                do_action("wp_theme_settings_field_$type", $option);
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            endforeach;
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php

                                endif;
                                ?>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }


        public function field_faq( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            $args			= isset( $option['args'] ) ? $option['args'] : array();

            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;


            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.faq-list-<?php echo esc_attr($id); ?> .faq-header', function() {
                        if(jQuery(this).parent().hasClass('active')){
                            jQuery(this).parent().removeClass('active');
                        }else{
                            jQuery(this).parent().addClass('active');
                        }
                    })
                })
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-faq-wrapper
            field-faq-wrapper-<?php echo esc_attr($id); ?>">
                <div class='faq-list faq-list-<?php echo esc_attr($id); ?>'>
                    <?php
                    foreach( $args as $key => $value ):
                        $title = $value['title'];
                        $link = $value['link'];
                        $content = $value['content'];
                        ?>
                        <div class="faq-item">
                            <div class="faq-header"><?php echo esc_html($title); ?></div>
                            <div class="faq-content"><?php echo esc_html($content); ?></div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>

            <?php
            return ob_get_clean();
        }




        public function field_grid( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $widths 		= isset( $option['width'] ) ? $option['width'] : array('768px'=>'100%','992px'=>'50%', '1200px'=>'30%', );
            $heights 		= isset( $option['height'] ) ? $option['height'] : array('768px'=>'auto','992px'=>'250px', '1200px'=>'250px', );


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-grid-wrapper
            field-grid-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach($args as $key=>$grid_item){
                    $title = isset($grid_item['title']) ? $grid_item['title'] : '';
                    $link = isset($grid_item['link']) ? $grid_item['link'] : '';
                    $thumb = isset($grid_item['thumb']) ? $grid_item['thumb'] : '';
                    ?>
                    <div class="item">
                        <div class="thumb"><a href="<?php echo esc_attr($link); ?>"><img src="<?php echo esc_attr($thumb); ?>"></img></a></div>
                        <div class="name"><a href="<?php echo esc_attr($link); ?>"><?php echo esc_html($title); ?></a></div>
                    </div>
                    <?php
                }
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                <?php
                if(!empty($widths)):
                    foreach ($widths as $screen_size=>$width):
                    $height = !empty($heights[$screen_size]) ? $heights[$screen_size] : 'auto';
                    ?>
                    @media screen and (min-width: <?php echo esc_attr($screen_size); ?>) {
                        .field-grid-wrapper-<?php echo esc_attr($id); ?> .item{
                            width: <?php echo esc_attr($width); ?>;
                        }
                        .field-grid-wrapper-<?php echo esc_attr($id); ?> .item .thumb{
                            height: <?php echo esc_attr($height); ?>;
                        }
                    }
                    <?php
                    endforeach;
                endif;
                ?>
            </style>

            <?php
            return ob_get_clean();
        }





        public function field_color_sets( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width			= isset( $args['width'] ) ? $args['width'] : "";
            $height			= isset( $args['height'] ) ? $args['height'] : "";
            $sets		    = isset( $option['sets'] ) ? $option['sets'] : array();
            //$option_value	= get_option( $id );
            $default		= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-sets-wrapper
            field-color-sets-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $sets as $key => $set ):

                    //var_dump($value);

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?>
                    <label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'>
                        <input name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>' value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>>
                        <?php
                        foreach ($set as $color):
                            ?>
                            <span class="color-srick" style="background-color: <?php echo esc_attr($color); ?>;"></span>
                            <?php

                        endforeach;
                        ?>


                        <span class="checked-icon"><i class="fas fa-check"></i></span>

                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();

        }


        public function field_image_link( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $links		= isset( $option['links'] ) ? $option['links'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-image-link-wrapper
            field-image-link-wrapper-<?php echo esc_attr($id); ?>">
                <?php


                    if(!empty($links))
                        foreach( $links as $key => $link ):



                            $checked = ( $link == $value ) ? "checked" : "";
                            ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                                    type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                                    value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>>
                            <img src="<?php echo esc_attr($link); ?>">
                            <span class="checked-icon"><i class="fas fa-check"></i></span>
                            </label><?php
                        endforeach;
                if(!in_array($value, $links)){
                    ?><label  class="checked" for='<?php echo esc_attr($id); ?>-custom'><input
                            type='radio' id='<?php echo esc_attr($id); ?>-custom'
                            value='<?php echo esc_attr($value); ?>' checked>
                    <img src="<?php echo esc_attr($value); ?>">
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                }


                ?>
                <div class="val-wrap">
                    <input class="link-val" name='<?php echo esc_attr($field_name); ?>' type="text" value="<?php echo esc_attr($value); ?>"> <span class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','mage-eventpress');?></span> <span class="ppof-button clear">Clear</span>
                </div>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            //$('#media_preview_<?php echo esc_attr($id); ?>').attr('src', attachment.url);
                            //$('#media_input_<?php echo esc_attr($id); ?>').val(attachment.url);
                            jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val(attachment.url);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });

                });
            </script>
            <style type="text/css">
                .field-image-link-wrapper-<?php echo esc_attr($id); ?> img{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-image-link-wrapper-<?php echo esc_attr($id); ?> .clear', function() {
                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val("");
                    })

                    jQuery(document).on('click', '.field-image-link-wrapper-<?php echo esc_attr($id); ?> img', function() {

                        var src = $(this).attr('src');
                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> .link-val').val(src);

                        jQuery('.field-image-link-wrapper-<?php echo esc_attr($id); ?> label').removeClass('checked');
                        if(jQuery(this).parent().hasClass('checked')){
                            jQuery(this).parent().removeClass('checked');
                        }else{
                            jQuery(this).parent().addClass('checked');
                        }
                    })
                })
            </script>

            <?php
            return ob_get_clean();

        }


        public function field_color_palette( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            //$option_value	= get_option( $id );
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-wrapper
            field-color-palette-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):

                    $checked = ( $key == $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>' type='radio' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo esc_attr($color); ?>" style="background-color: <?php
                    echo esc_attr($color); ?>" class="sw-button"></span>
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();

        }




        public function field_color_palette_multi( $option ){

            $id				= isset( $option['id'] ) ? $option['id'] : "";

            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();

            $args			= isset( $option['args'] ) ? $option['args'] : array();
            $width				= isset( $args['width'] ) ? $args['width'] : "";
            $height				= isset( $args['height'] ) ? $args['height'] : "";
            $colors			= isset( $option['colors'] ) ? $option['colors'] : array();
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-color-palette-multi-wrapper
            field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                foreach( $colors as $key => $color ):
                    $checked = is_array( $value ) && in_array( $key, $value ) ? "checked" : "";
                    ?><label  class="<?php echo esc_attr($checked); ?>" for='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'><input
                            name='<?php echo esc_attr($field_name); ?>[]' type='checkbox' id='<?php echo esc_attr($id); ?>-<?php echo esc_attr($key); ?>'
                            value='<?php echo esc_attr($key); ?>' <?php echo esc_attr($checked); ?>><span title="<?php echo esc_attr($color); ?>" style="background-color: <?php
                    echo esc_attr($color); ?>" class="sw-button"></span>
                    <span class="checked-icon"><i class="fas fa-check"></i></span>
                    </label><?php
                endforeach;
                ?>
                <div class="error-mgs"></div>
            </div>
            <style type="text/css">
                .field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?> .sw-button{
                    transition: ease all 1s;
                <?php if(!empty($width)):  ?>
                    width: <?php echo esc_attr($width); ?>;
                <?php endif; ?>
                <?php if(!empty($height)):  ?>
                    height: <?php echo esc_attr($height); ?>;
                <?php endif; ?>
                }
                .field-color-palette-multi-wrapper-<?php echo esc_attr($id); ?> label:hover .sw-button{
                }
            </style>


            <?php
            return ob_get_clean();
        }




        public function field_media( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $placeholder	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";

            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $value			= isset( $option['value'] ) ? $option['value'] : '';
            $value          = !empty($value) ?  $value : $default;

            $media_url	= wp_get_attachment_url( $value );
            $media_type	= get_post_mime_type( $value );
            $media_title= get_the_title( $value );
            $media_url = !empty($media_url) ? $media_url : $placeholder;

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-wrapper
            field-media-wrapper-<?php echo esc_attr($id); ?>">
                <div class='media_preview' style='width: 150px;margin-bottom: 10px;background: #eee;padding: 5px;    text-align: center;'>
                    <?php

                    if( "audio/mpeg" == $media_type ){
                        ?>
                        <div id='media_preview_$id' class='dashicons dashicons-format-audio' style='font-size: 70px;display: inline;'></div>
                        <div><?php echo esc_html($media_title); ?></div>
                        <?php
                    }
                    else {
                        ?>
                        <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                        <?php
                    }
                    ?>
                </div>
                <input type='hidden' name='<?php echo esc_attr($field_name); ?>' id='media_input_<?php echo esc_attr($id); ?>' value='<?php echo esc_attr($value); ?>' />
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','mage-eventpress');?></div><div class='ppof-button clear' id='media_clear_<?php echo esc_attr($id); ?>'><?php echo __('Clear','mage-eventpress');?></div>
                <div class="error-mgs"></div>
            </div>

            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            $('#media_preview_<?php echo esc_attr($id); ?>').attr('src', attachment.url);
                            $('#media_input_<?php echo esc_attr($id); ?>').val(attachment.id);
                            wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php echo esc_attr($id); ?>').click(function() {
                        $('#media_input_<?php echo esc_attr($id); ?>').val('');
                        $('#media_preview_<?php echo esc_attr($id); ?>').attr('src','');
                    })

                });
            </script>

            <?php
            return ob_get_clean();
        }




        public function field_media_multi( $option ){

            $id			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $remove_text			= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
            $default			= isset( $option['default'] ) ? $option['default'] : '';
            $values			= isset( $option['value'] ) ? $option['value'] : '';

            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;



            ob_start();
            wp_enqueue_media();

            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-media-multi-wrapper
            field-media-multi-wrapper-<?php echo esc_attr($id); ?>">
                <div class='ppof-button upload' id='media_upload_<?php echo esc_attr($id); ?>'><?php echo __('Upload','mage-eventpress');?></div><div class='ppof-button clear'
                                                                                          id='media_clear_<?php echo
                                                                                          $id;
                                                                                          ?>'><?php echo __('Clear','mage-eventpress');?></div>
                <div class="media-list media-list-<?php echo esc_attr($id); ?> sortable">
                    <?php
                    if(!empty($values) && is_array($values)):
                        foreach ($values as $value ):
                            $media_url	= wp_get_attachment_url( $value );
                            $media_type	= get_post_mime_type( $value );
                            $media_title= get_the_title( $value );
                            ?>
                            <div class="item">
                                <span class="remove" onclick="jQuery(this).parent().remove()"><?php echo esc_html($remove_text); ?></span>
                                <span class="sort" >sort</span>
                                <img id='media_preview_<?php echo esc_attr($id); ?>' src='<?php echo esc_attr($media_url); ?>' style='width:100%'/>
                                <div class="item-title"><?php echo esc_html($media_title); ?></div>
                                <input type='hidden' name='<?php echo esc_attr($field_name); ?>[]' value='<?php echo esc_attr($value); ?>' />
                            </div>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <div class="error-mgs"></div>
            </div>
            <script>jQuery(document).ready(function($){
                    $('#media_upload_<?php echo esc_attr($id); ?>').click(function() {
                        //var send_attachment_bkp = wp.media.editor.send.attachment;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            attachment_id = attachment.id;
                            attachment_url = attachment.url;
                            html = '<div class="item">';
                            html += '<span class="remove" onclick="jQuery(this).parent().remove()"><?php echo esc_html($remove_text); ?></span>';
                            html += '<img src="'+attachment_url+'" style="width:100%"/>';
                            html += '<input type="hidden" name="<?php echo esc_attr($field_name); ?>[]" value="'+attachment_id+'" />';
                            html += '</div>';
                            $('.media-list-<?php echo esc_attr($id); ?>').append(html);
                            //wp.media.editor.send.attachment = send_attachment_bkp;
                        }
                        wp.media.editor.open($(this));
                        return false;
                    });
                    $('#media_clear_<?php echo esc_attr($id); ?>').click(function() {
                        $('.media-list-<?php echo esc_attr($id); ?> .item').remove();
                    })
                });
            </script>

            <?php
            return ob_get_clean();
        }




        public function field_custom_html( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            $args 			= isset( $option['args'] ) ? $option['args'] : "";
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $html 			= isset( $option['html'] ) ? $option['html'] : "";


            if(!empty($conditions)):

                $depends = '';

                $field = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like = isset($conditions['like']) ? $conditions['like'] : '';
                $strict = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min = isset($conditions['min']) ? $conditions['min'] : '';
                $max = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';

            endif;




            ob_start();
            ?>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?>
                    id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-custom-html-wrapper
            field-custom-html-wrapper-<?php echo esc_attr($id); ?>">
                <?php
                echo esc_html($html);
                ?>
                <div class="error-mgs"></div>
            </div>

            <?php

            return ob_get_clean();


        }

        function get_form_title($arr,$val){
            foreach ($arr as $_arr) {
                $name[] = $val[$_arr];
            }

            return join(' - ',$name);
        }

        public function field_repeatable( $option ){

            $id 			= isset( $option['id'] ) ? $option['id'] : "";
            if(empty($id)) return;
            $field_name 	= isset( $option['field_name'] ) ? $option['field_name'] : $id;
            $conditions 	= isset( $option['conditions'] ) ? $option['conditions'] : array();
            $sortable 	    = isset( $option['sortable'] ) ? $option['sortable'] : true;
            $collapsible 	= isset( $option['collapsible'] ) ? $option['collapsible'] : true;
            $placeholder 	= isset( $option['placeholder'] ) ? $option['placeholder'] : "";
            $values			= isset( $option['value'] ) ? $option['value'] : '';
            $btntext		= isset( $option['btn_text'] ) ? $option['btn_text'] : 'Add';
            $fields 		= isset( $option['fields'] ) ? $option['fields'] : array();
            $title_field 	= isset( $option['title_field'] ) ? $option['title_field'] : '';
            $remove_text 	= isset( $option['remove_text'] ) ? $option['remove_text'] : '<i class="fas fa-times"></i>';
            $limit 	        = isset( $option['limit'] ) ? $option['limit'] : '';
            $args 	        = isset( $option['args'] ) ? $option['args'] : '';
            $args			= is_array( $args ) ? $args : $this->args_from_string( $args );
            $field_id       = $id;
            $field_name     = !empty( $field_name ) ? $field_name : $id;



            $new_title      =  explode('/',$title_field);
            $title_field    = $new_title;            
            foreach ($fields as $key => $value) {            
                # code...
                $new[$key]['type']      = $fields[$key]['type'];
                $new[$key]['default']   = $fields[$key]['default'];
                $new[$key]['item_id']   = $fields[$key]['item_id'];
                $new[$key]['name']      = $fields[$key]['name'];
                if(is_array($value) && array_key_exists( 'args', $value )){
                 $new[$key]['args']      = !is_array($fields[$key]['args']) ? $this->args_from_string($fields[$key]['args']) : $fields[$key]['args'];
                }
                 
            }
            $fields = $new;
           

            if(!empty($conditions)):

                $depends = '';

                $field      = isset($conditions['field']) ? $conditions['field'] :'';
                $cond_value = isset($conditions['value']) ? $conditions['value']: '';
                $type       = isset($conditions['type']) ? $conditions['type'] : '';
                $pattern    = isset($conditions['pattern']) ? $conditions['pattern'] : '';
                $modifier   = isset($conditions['modifier']) ? $conditions['modifier'] : '';
                $like       = isset($conditions['like']) ? $conditions['like'] : '';
                $strict     = isset($conditions['strict']) ? $conditions['strict'] : '';
                $empty      = isset($conditions['empty']) ? $conditions['empty'] : '';
                $sign       = isset($conditions['sign']) ? $conditions['sign'] : '';
                $min        = isset($conditions['min']) ? $conditions['min'] : '';
                $max        = isset($conditions['max']) ? $conditions['max'] : '';

                $depends .= "{'[name=".$field."]':";
                $depends .= '{';

                if(!empty($type)):
                    $depends .= "'type':";
                    $depends .= "'".$type."'";
                endif;

                if(!empty($modifier)):
                    $depends .= ",'modifier':";
                    $depends .= "'".$modifier."'";
                endif;

                if(!empty($like)):
                    $depends .= ",'like':";
                    $depends .= "'".$like."'";
                endif;

                if(!empty($strict)):
                    $depends .= ",'strict':";
                    $depends .= "'".$strict."'";
                endif;

                if(!empty($empty)):
                    $depends .= ",'empty':";
                    $depends .= "'".$empty."'";
                endif;

                if(!empty($sign)):
                    $depends .= ",'sign':";
                    $depends .= "'".$sign."'";
                endif;

                if(!empty($min)):
                    $depends .= ",'min':";
                    $depends .= "'".$min."'";
                endif;

                if(!empty($max)):
                    $depends .= ",'max':";
                    $depends .= "'".$max."'";
                endif;
                if(!empty($cond_value)):
                    $depends .= ",'value':";
                    if(is_array($cond_value)):
                        $count= count($cond_value);
                        $i = 1;
                        $depends .= "[";
                        foreach ($cond_value as $val):
                            $depends .= "'".$val."'";
                            if($i<$count)
                                $depends .= ",";
                            $i++;
                        endforeach;
                        $depends .= "]";
                    else:
                        $depends .= "[";
                        $depends .= "'".$cond_value."'";
                        $depends .= "]";
                    endif;
                endif;
                $depends .= '}}';
            endif;
            ob_start();
            ?>
            <script>
                jQuery(document).ready(function($) {
                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .collapsible .header .title-text', function() {
                        if(jQuery(this).parent().parent().hasClass('active')){
                            jQuery(this).parent().parent().removeClass('active');
                        }else{
                            jQuery(this).parent().parent().addClass('active');
                        }
                    })

                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .clone',function(){

                        //event.preventDefault();

                        index_id = $(this).attr('index_id');
                        now = jQuery.now();
                        <?php
                        if(!empty($limit)):


                        ?>
                        var limit = <?php  echo esc_attr($limit); ?>;
                        var node_count = $( ".field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                        if(limit > node_count){
                            $( this ).parent().parent().clone().appendTo('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                           // html = $( this ).parent().parent().clone();
                            //var html_new = html.replace(index_id, now);
                            //jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html_new);
                            //console.log(html);

                        }else{
                            jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                        }
                        <?php
                        else:
                        ?>
                        $( this ).parent().clone().appendTo('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list' );
                        <?php
                        endif;
                        ?>
                    })

                    jQuery(document).on('click', '.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .add-item', function() {
                        now = jQuery.now();
                        fields_arr = <?php echo json_encode($fields); ?>;
                        html  = '<div class="item-wrap collapsible"><div class="header">';
                        html += '<span class="button title-text"><i class="fas fa-angle-double-down"></i> Expand</span> ';
                        html += '<span class="button remove" ' +
                            'onclick="jQuery(this).parent().parent().remove()"><?php echo mep_esc_html($remove_text); ?></span> ';
                        
                        <?php if($sortable):?>
                        html += '<span class="button sort" ><i class="fas fa-grip-vertical"></i></span>';
                        <?php endif; ?>
                        html += '</div>';
                         // html += ' <span  class="title-text">#'+now+'</span></div>';
                        fields_arr.forEach(function(element) {
                            type = element.type;
                            item_id = element.item_id;
                            default_val = element.default;
                            html+='<div class="item">';
                            <?php if($collapsible):?>
                            html+='<div class="content">';
                            <?php endif; ?>
                            html+='<div class="item-title">'+element.name+'</div>';
                            if(type == 'text'){
                                html+='<input type="text" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'number'){
                                html+='<input type="number" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'tel'){
                                html+='<input type="tel" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'time'){
                                html+='<input type="time" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'url'){
                                html+='<input type="url" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'date'){
                                html+='<input type="date" value="'+default_val+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'month'){
                                html+='<input type="month" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'search'){
                                html+='<input type="search" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'color'){
                                html+='<input type="color" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'email'){
                                html+='<input type="email" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                            }else if(type == 'textarea'){

                                html+='<textarea id="<?php echo esc_attr($field_name); ?>'+now+'" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"></textarea>';
                               
                                jQuery(function(){
                                    tinymce.init({
                                        selector:"#<?php echo esc_attr($field_name); ?>"+now,
                                        menubar: true,
                                        relative_urls : 0,
                                        remove_script_host : 0,
                                        toolbar: 'undo redo link formatselect bold italic backcolor alignleft aligncenter alignright alignjustify bullist numlist outdent indent removeformat fullscreen',
                                                        plugins: 'fullscreen link'
                                    });
                                });                          
                                
                            }else if(type == 'select'){
                                args = element.args;
                                html+='<select name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']">';
                                for(argKey in args){                                                                       
                                    html+='<option value="'+argKey+'">'+args[argKey]+'</option>';
                                }
                                html+='</select>';
                            }else if(type == 'radio'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="radio" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }else if(type == 'checkbox'){
                                args = element.args;
                                for(argKey in args){
                                    html+='<label>';
                                    html+='<input value="'+argKey+'" type="checkbox" name="<?php echo esc_attr($field_name); ?>['+now+']['+element.item_id+']"/>';
                                    html+= args[argKey];
                                    html+='</label ><br/>';
                                }
                            }
                            <?php if($collapsible):?>
                            html+='</div>';
                            <?php endif; ?>
                            html+='</div>';
                        });
                        html+='</div>';

                        <?php
                        if(!empty($limit)):
                            ?>
                            var limit = <?php  echo esc_attr($limit); ?>;
                            var node_count = $( ".field-repeatable-wrapper-<?php echo esc_attr($id); ?> .field-list .item-wrap" ).size();
                            if(limit > node_count){
                                jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                            }else{
                                jQuery('.field-repeatable-wrapper-<?php echo esc_attr($id); ?> .error-mgs').html('Sorry! you can add max '+limit+' item').stop().fadeIn(400).delay(3000).fadeOut(400);
                            }

                            <?php
                        else:
                            ?>
                            jQuery('.<?php echo 'field-repeatable-wrapper-'.$id; ?> .field-list').append(html);
                            <?php
                        endif;
                        ?>
                    })
                });
            </script>
            <div <?php if(!empty($depends)) {?> data-depends="[<?php echo esc_attr($depends); ?>]" <?php } ?> id="field-wrapper-<?php echo esc_attr($id); ?>" class="<?php if(!empty($depends)) echo 'dependency-field'; ?> field-wrapper field-repeatable-wrapper
            field-repeatable-wrapper-<?php echo esc_attr($id); ?>">
                
                <div class="field-list <?php if($sortable){ echo 'sortable'; }?>" id="<?php echo esc_attr($id); ?>">
                    <?php
                    if(!empty($values)):
                        $count = 1;
                        foreach ($values as $index=>$val):
                            $title_field_val = !empty($title_field) ? $this->get_form_title($title_field,$val) : '==> Click to Expand';
                            ?>
                            <div class="item-wrap <?php if($collapsible) echo 'collapsible'; ?>">
                                <?php if($collapsible):?>
                                <div class="header">
                                    <?php endif; ?>                                  
                                    <?php if($sortable):?>
                                        <span class="button sort"><i class="fas fa-arrows-alt"></i></span>
                                    <?php endif; ?>
                                    <span class="title-text" style="cursor:pointer;display: inline-block;width: 84%;"><?php echo mep_esc_html($title_field_val); ?></span>
                                    <span class="button remove" onclick="jQuery(this).parent().parent().remove()"><?php echo mep_esc_html($remove_text); ?></span>
                                    <?php if($collapsible):?>
                                </div>
                            <?php endif; ?>
                                <?php foreach ($fields as $field_index => $field):
                                    $type               = $field['type'];
                                    $item_id            = $field['item_id'];
                                    $name               = $field['name'];
                                    $title_field_class = ($title_field == $field_index) ? 'title-field':'';
                                    ?>
                                    <div class="item <?php echo esc_attr($title_field_class); ?>">
                                        <?php if($collapsible):?>
                                        <div class="content">
                                            <?php endif; ?>
                                            <div class="item-title"><?php echo esc_attr($name); ?></div>
                                            <?php if($type == 'text'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="text" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'number'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="number" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'url'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="url" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'tel'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="tel" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'time'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="time" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'search'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="search" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'month'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="month" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'color'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="color" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'date'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="date" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'email'):
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <input type="email" class="regular-text" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" placeholder="" value="<?php echo esc_html($value); ?>">
                                            <?php elseif($type == 'textarea'):
                                                $default    = isset($field['default']) ? $field['default'] : '';
                                                $_value     = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                $__value    = str_replace('<br />', PHP_EOL, html_entity_decode($_value));;
                                                ?>
                                                
                                                <?php $rnd = rand(); ?>
                                                <textarea id="<?php echo esc_attr($field_name).$rnd; ?>" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]"><?php echo $__value; ?></textarea>
                                                <script>
                                                jQuery(function(){
                                                    tinymce.init({
                                                        selector: "#<?php echo esc_attr($field_name).$rnd; ?>",
                                                        menubar: true,
                                                        relative_urls : 0,
                                                        remove_script_host : 0,                                                        
                                                        toolbar: 'undo redo link formatselect bold italic backcolor alignleft aligncenter alignright alignjustify bullist numlist outdent indent removeformat fullscreen',
                                                        plugins: 'fullscreen link'
                                                    });
                                                });
                                                </script>
                                              
                                            <?php elseif($type == 'select'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;

                                               
                                                ?>
                                                <select class="" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]">
                                                <?php                                                    
                                                   if(!is_array($args)){                                                   
                                                   $this->args_from_string($args);
                                                   }else{                                                    
                                                        foreach ($args as $argIndex => $argName):
                                                        $selected = ($argIndex == $value) ? 'selected' : '';
                                                        ?>
                                                        <option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option>
                                                    <?php endforeach; }?>
                                                </select>
                                            <?php elseif($type == 'radio'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php 
                                                if(!is_array($args)){                                                   
                                                    $this->args_from_string($args);
                                                }else{                                                  
                                                foreach ($args as $argIndex => $argName):
                                                $checked = ($argIndex == $value) ? 'checked' : '';
                                                
                                                ?>
                                                <label class="" >
                                                    <input  type="radio" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>]" <?php echo esc_attr($checked); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></input>
                                                </label>
                                            <?php endforeach; } ?>
                                            <?php elseif($type == 'checkbox'):
                                                $args = isset($field['args']) ? $field['args'] : array();
                                                $default = isset($field['default']) ? $field['default'] : '';
                                                $value = !empty($val[$item_id]) ? $val[$item_id] : $default;
                                                ?>
                                                <?php 
                                                
                                                foreach ($args as $argIndex => $argName):
                                                $value = is_array($value) ? $value : array();
                                                // print_r($value);
                                                $checked = in_array($argIndex, $value ) ? 'checked' : '';
                                                // $checked = isset($argIndex) ? 'checked' : '';
                                                ?>
                                                <label class="" >
                                                    <input  type="checkbox" name="<?php echo esc_attr($field_name); ?>[<?php echo esc_attr($index); ?>][<?php echo esc_attr($item_id); ?>][]" <?php echo esc_attr($checked); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></input>
                                                </label>
                                            <?php endforeach; ?>
                                            <?php
                                            else:
                                                do_action('repeatable_custom_input_field_'.$type, $field);
                                                ?>
                                            <?php endif;?>
                                            <?php if($collapsible):?>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php
                            //endforeach;
                            $count++;
                        endforeach;
                        ?>

                        <?php
                    else:
                        ?>
                    <?php
                    endif;
                    ?>                    
                </div>
                <div class="error-mgs"></div>
                <div class="ppof-button add-item"><i class="fas fa-plus-circle"></i> <?php echo esc_html($btntext); ?></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public function get_tax_data($args){
            foreach ($this->get_rep_taxonomies_array( $args ) as $argIndex => $argName):
            $selected = ($argIndex == $argName) ? 'selected' : ''; ?><option <?php echo esc_attr($selected); ?>  value="<?php echo esc_attr($argIndex); ?>"><?php echo esc_html($argName); ?></option> <?php endforeach;
        }


        public function args_from_string( $string ){

            if( strpos( $string, 'PAGES_IDS_ARRAY' )    !== false ) return $this->get_pages_array();
            if( strpos( $string, 'POSTS_IDS_ARRAY' )    !== false ) return $this->get_posts_array();
            if( strpos( $string, 'POST_TYPES_ARRAY' )   !== false ) return $this->get_post_types_array();
            if( strpos( $string, 'TAX_' )               !== false ) return $this->get_taxonomies_array( $string );
            if( strpos( $string, 'TAXN_' )               !== false ) return $this->get_rep_taxonomies_array( $string );
            if( strpos( $string, 'CPT_' )               !== false ) return $this->get_cpt_array( $string );
            if( strpos( $string, 'USER_ROLES' )         !== false ) return $this->get_user_roles_array();
            if( strpos( $string, 'USER_IDS_ARRAY' )     !== false ) return $this->get_user_ids_array();
            if( strpos( $string, 'MENUS' )              !== false ) return $this->get_menus_array();
            if( strpos( $string, 'SIDEBARS_ARRAY' )     !== false ) return $this->get_sidebars_array();
            if( strpos( $string, 'THUMB_SIEZS_ARRAY' )  !== false ) return $this->get_thumb_sizes_array();
            if( strpos( $string, 'FONTAWESOME_ARRAY' )  !== false ) return $this->get_font_aws_array();
            return array();
        }




        public function get_rep_taxonomies_array( $string ){

            $taxonomies = array();

            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );

            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');

            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");

            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );

            foreach( $terms as $term ) $taxonomies[ $term->name ] = $term->name;

            return $taxonomies;
        }



        public function get_taxonomies_array( $string ){
            $taxonomies = array();
            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );
            if( isset( $matches[1][0] ) ) $taxonomy = $matches[1][0];
            else throw new Pick_error('Invalid taxonomy declaration !');
            if( ! taxonomy_exists( $taxonomy ) ) throw new Pick_error("Taxonomy <strong>$taxonomy</strong> doesn't exists !");
            $terms = get_terms( $taxonomy, array(
                'hide_empty' => false,
            ) );
            foreach( $terms as $term ) $taxonomies[ $term->term_id ] = $term->name;
            return $taxonomies;
        }

        public function get_cpt_array( $string ){
            preg_match_all( "/\%([^\]]*)\%/", $string, $matches );
            $cpt_name = $matches[1][0];
            $defaults = array(
                'numberposts'      => -1,
                'post_type' => $cpt_name,
            );
            $cpt_arr = get_posts($defaults);
            $cpt = array();
            foreach ($cpt_arr as $_cpt_arr) {
                $cpt[$_cpt_arr->ID]  = $_cpt_arr->post_title;
            }
            return $cpt;
        }

        public function get_user_ids_array(){
            $user_ids = array();
            $users = get_users();
            foreach( $users as $user ) $user_ids[ $user->ID ] = $user->display_name. '(#'.$user->ID.')';
            return apply_filters( 'USER_IDS_ARRAY', $user_ids );
        }


        public function get_pages_array(){
            $pages_array = array();
            foreach( get_pages() as $page ) $pages_array[ $page->ID ] = $page->post_title;
            return apply_filters( 'PAGES_IDS_ARRAY', $pages_array );
        }

        public function get_menus_array(){
            $menus = get_registered_nav_menus();
            return apply_filters( 'MENUS_ARRAY', $menus );
        }

        public function get_sidebars_array(){

            global $wp_registered_sidebars;
            $sidebars = $wp_registered_sidebars;

            foreach ($sidebars as $index => $sidebar){

                $sidebars_name[$index] = $sidebar['name'];
            }


            return apply_filters( 'SIDEBARS_ARRAY', $sidebars_name );
        }

        public function get_user_roles_array(){
            require_once ABSPATH . 'wp-admin/includes/user.php';

            $roles = get_editable_roles();

            foreach ($roles as $index => $data){

                $role_name[$index] = $data['name'];
            }

            return apply_filters( 'USER_ROLES', $role_name );
        }



        public function get_post_types_array(){

            $post_types = get_post_types('', 'names' );
            $pages_array = array();
            foreach( $post_types as $index => $name ) $pages_array[ $index ] = $name;

            return apply_filters( 'POST_TYPES_ARRAY', $pages_array );
        }


        public function get_posts_array(){

            $posts_array = array();
            foreach( get_posts(array('posts_per_page'=>-1)) as $page ) $posts_array[ $page->ID ] = $page->post_title;

            return apply_filters( 'POSTS_IDS_ARRAY', $posts_array );
        }


        public function get_thumb_sizes_array(){

            $get_intermediate_image_sizes =  get_intermediate_image_sizes();
            $get_intermediate_image_sizes = array_merge($get_intermediate_image_sizes,array('full'));
            $thumb_sizes_array = array();

            foreach( $get_intermediate_image_sizes as $key => $name ):
                $size_key = str_replace('_', ' ',$name);
                $size_key = str_replace('-', ' ',$size_key);
                $size_name = ucfirst($size_key);
                $thumb_sizes_array[$name] = $size_name;
            endforeach;

            return apply_filters( 'THUMB_SIEZS_ARRAY', $get_intermediate_image_sizes );
        }


        public function get_font_aws_array(){

            $fonts_arr = array (
                'fab fa-500px' => '500px',
                'fab fa-accessible-icon' => 'accessible-icon',
                'fab fa-accusoft' => 'accusoft',
                'fas fa-address-book' => 'address-book',
                'far fa-address-book' => 'address-book',
                'fas fa-address-card' => 'address-card',
                'far fa-address-card' => 'address-card',
                'fas fa-adjust' => 'adjust',
                'fab fa-adn' => 'adn',
                'fab fa-adversal' => 'adversal',
                'fab fa-affiliatetheme' => 'affiliatetheme',
                'fab fa-algolia' => 'algolia',
                'fas fa-align-center' => 'align-center',
                'fas fa-align-justify' => 'align-justify',
                'fas fa-align-left' => 'align-left',
                'fas fa-align-right' => 'align-right',
                'fas fa-allergies' => 'allergies',
                'fab fa-amazon' => 'amazon',
                'fab fa-amazon-pay' => 'amazon-pay',
                'fas fa-ambulance' => 'ambulance',
                'fas fa-american-sign-language-interpreting' => 'american-sign-language-interpreting',
                'fab fa-amilia' => 'amilia',
                'fas fa-anchor' => 'anchor',
                'fab fa-android' => 'android',
                'fab fa-angellist' => 'angellist',
                'fas fa-angle-double-down' => 'angle-double-down',
                'fas fa-angle-double-left' => 'angle-double-left',
                'fas fa-angle-double-right' => 'angle-double-right',
                'fas fa-angle-double-up' => 'angle-double-up',
                'fas fa-angle-down' => 'angle-down',
                'fas fa-angle-left' => 'angle-left',
                'fas fa-angle-right' => 'angle-right',
                'fas fa-angle-up' => 'angle-up',
                'fab fa-angrycreative' => 'angrycreative',
                'fab fa-angular' => 'angular',
                'fab fa-app-store' => 'app-store',
                'fab fa-app-store-ios' => 'app-store-ios',
                'fab fa-apper' => 'apper',
                'fab fa-apple' => 'apple',
                'fab fa-apple-pay' => 'apple-pay',
                'fas fa-archive' => 'archive',
                'fas fa-arrow-alt-circle-down' => 'arrow-alt-circle-down',
                'far fa-arrow-alt-circle-down' => 'arrow-alt-circle-down',
                'fas fa-arrow-alt-circle-left' => 'arrow-alt-circle-left',
                'far fa-arrow-alt-circle-left' => 'arrow-alt-circle-left',
                'fas fa-arrow-alt-circle-right' => 'arrow-alt-circle-right',
                'far fa-arrow-alt-circle-right' => 'arrow-alt-circle-right',
                'fas fa-arrow-alt-circle-up' => 'arrow-alt-circle-up',
                'far fa-arrow-alt-circle-up' => 'arrow-alt-circle-up',
                'fas fa-arrow-circle-down' => 'arrow-circle-down',
                'fas fa-arrow-circle-left' => 'arrow-circle-left',
                'fas fa-arrow-circle-right' => 'arrow-circle-right',
                'fas fa-arrow-circle-up' => 'arrow-circle-up',
                'fas fa-arrow-down' => 'arrow-down',
                'fas fa-arrow-left' => 'arrow-left',
                'fas fa-arrow-right' => 'arrow-right',
                'fas fa-arrow-up' => 'arrow-up',
                'fas fa-arrows-alt' => 'arrows-alt',
                'fas fa-arrows-alt-h' => 'arrows-alt-h',
                'fas fa-arrows-alt-v' => 'arrows-alt-v',
                'fas fa-assistive-listening-systems' => 'assistive-listening-systems',
                'fas fa-asterisk' => 'asterisk',
                'fab fa-asymmetrik' => 'asymmetrik',
                'fas fa-at' => 'at',
                'fab fa-audible' => 'audible',
                'fas fa-audio-description' => 'audio-description',
                'fab fa-autoprefixer' => 'autoprefixer',
                'fab fa-avianex' => 'avianex',
                'fab fa-aviato' => 'aviato',
                'fab fa-aws' => 'aws',
                'fas fa-backward' => 'backward',
                'fas fa-balance-scale' => 'balance-scale',
                'fas fa-ban' => 'ban',
                'fas fa-band-aid' => 'band-aid',
                'fab fa-bandcamp' => 'bandcamp',
                'fas fa-barcode' => 'barcode',
                'fas fa-bars' => 'bars',
                'fas fa-baseball-ball' => 'baseball-ball',
                'fas fa-basketball-ball' => 'basketball-ball',
                'fas fa-bath' => 'bath',
                'fas fa-battery-empty' => 'battery-empty',
                'fas fa-battery-full' => 'battery-full',
                'fas fa-battery-half' => 'battery-half',
                'fas fa-battery-quarter' => 'battery-quarter',
                'fas fa-battery-three-quarters' => 'battery-three-quarters',
                'fas fa-bed' => 'bed',
                'fas fa-beer' => 'beer',
                'fab fa-behance' => 'behance',
                'fab fa-behance-square' => 'behance-square',
                'fas fa-bell' => 'bell',
                'far fa-bell' => 'bell',
                'fas fa-bell-slash' => 'bell-slash',
                'far fa-bell-slash' => 'bell-slash',
                'fas fa-bicycle' => 'bicycle',
                'fab fa-bimobject' => 'bimobject',
                'fas fa-binoculars' => 'binoculars',
                'fas fa-birthday-cake' => 'birthday-cake',
                'fab fa-bitbucket' => 'bitbucket',
                'fab fa-bitcoin' => 'bitcoin',
                'fab fa-bity' => 'bity',
                'fab fa-black-tie' => 'black-tie',
                'fab fa-blackberry' => 'blackberry',
                'fas fa-blind' => 'blind',
                'fab fa-blogger' => 'blogger',
                'fab fa-blogger-b' => 'blogger-b',
                'fab fa-bluetooth' => 'bluetooth',
                'fab fa-bluetooth-b' => 'bluetooth-b',
                'fas fa-bold' => 'bold',
                'fas fa-bolt' => 'bolt',
                'fas fa-bomb' => 'bomb',
                'fas fa-book' => 'book',
                'fas fa-bookmark' => 'bookmark',
                'far fa-bookmark' => 'bookmark',
                'fas fa-bowling-ball' => 'bowling-ball',
                'fas fa-box' => 'box',
                'fas fa-box-open' => 'box-open',
                'fas fa-boxes' => 'boxes',
                'fas fa-braille' => 'braille',
                'fas fa-briefcase' => 'briefcase',
                'fas fa-briefcase-medical' => 'briefcase-medical',
                'fab fa-btc' => 'btc',
                'fas fa-bug' => 'bug',
                'fas fa-building' => 'building',
                'far fa-building' => 'building',
                'fas fa-bullhorn' => 'bullhorn',
                'fas fa-bullseye' => 'bullseye',
                'fas fa-burn' => 'burn',
                'fab fa-buromobelexperte' => 'buromobelexperte',
                'fas fa-bus' => 'bus',
                'fab fa-buysellads' => 'buysellads',
                'fas fa-calculator' => 'calculator',
                'fas fa-calendar' => 'calendar',
                'far fa-calendar' => 'calendar',
                'fas fa-calendar-alt' => 'calendar-alt',
                'far fa-calendar-alt' => 'calendar-alt',
                'fas fa-calendar-check' => 'calendar-check',
                'far fa-calendar-check' => 'calendar-check',
                'fas fa-calendar-minus' => 'calendar-minus',
                'far fa-calendar-minus' => 'calendar-minus',
                'fas fa-calendar-plus' => 'calendar-plus',
                'far fa-calendar-plus' => 'calendar-plus',
                'fas fa-calendar-times' => 'calendar-times',
                'far fa-calendar-times' => 'calendar-times',
                'fas fa-camera' => 'camera',
                'fas fa-camera-retro' => 'camera-retro',
                'fas fa-capsules' => 'capsules',
                'fas fa-car' => 'car',
                'fas fa-caret-down' => 'caret-down',
                'fas fa-caret-left' => 'caret-left',
                'fas fa-caret-right' => 'caret-right',
                'fas fa-caret-square-down' => 'caret-square-down',
                'far fa-caret-square-down' => 'caret-square-down',
                'fas fa-caret-square-left' => 'caret-square-left',
                'far fa-caret-square-left' => 'caret-square-left',
                'fas fa-caret-square-right' => 'caret-square-right',
                'far fa-caret-square-right' => 'caret-square-right',
                'fas fa-caret-square-up' => 'caret-square-up',
                'far fa-caret-square-up' => 'caret-square-up',
                'fas fa-caret-up' => 'caret-up',
                'fas fa-cart-arrow-down' => 'cart-arrow-down',
                'fas fa-cart-plus' => 'cart-plus',
                'fab fa-cc-amazon-pay' => 'cc-amazon-pay',
                'fab fa-cc-amex' => 'cc-amex',
                'fab fa-cc-apple-pay' => 'cc-apple-pay',
                'fab fa-cc-diners-club' => 'cc-diners-club',
                'fab fa-cc-discover' => 'cc-discover',
                'fab fa-cc-jcb' => 'cc-jcb',
                'fab fa-cc-mastercard' => 'cc-mastercard',
                'fab fa-cc-paypal' => 'cc-paypal',
                'fab fa-cc-stripe' => 'cc-stripe',
                'fab fa-cc-visa' => 'cc-visa',
                'fab fa-centercode' => 'centercode',
                'fas fa-certificate' => 'certificate',
                'fas fa-chart-area' => 'chart-area',
                'fas fa-chart-bar' => 'chart-bar',
                'far fa-chart-bar' => 'chart-bar',
                'fas fa-chart-line' => 'chart-line',
                'fas fa-chart-pie' => 'chart-pie',
                'fas fa-check' => 'check',
                'fas fa-check-circle' => 'check-circle',
                'far fa-check-circle' => 'check-circle',
                'fas fa-check-square' => 'check-square',
                'far fa-check-square' => 'check-square',
                'fas fa-chess' => 'chess',
                'fas fa-chess-bishop' => 'chess-bishop',
                'fas fa-chess-board' => 'chess-board',
                'fas fa-chess-king' => 'chess-king',
                'fas fa-chess-knight' => 'chess-knight',
                'fas fa-chess-pawn' => 'chess-pawn',
                'fas fa-chess-queen' => 'chess-queen',
                'fas fa-chess-rook' => 'chess-rook',
                'fas fa-chevron-circle-down' => 'chevron-circle-down',
                'fas fa-chevron-circle-left' => 'chevron-circle-left',
                'fas fa-chevron-circle-right' => 'chevron-circle-right',
                'fas fa-chevron-circle-up' => 'chevron-circle-up',
                'fas fa-chevron-down' => 'chevron-down',
                'fas fa-chevron-left' => 'chevron-left',
                'fas fa-chevron-right' => 'chevron-right',
                'fas fa-chevron-up' => 'chevron-up',
                'fas fa-child' => 'child',
                'fab fa-chrome' => 'chrome',
                'fas fa-circle' => 'circle',
                'far fa-circle' => 'circle',
                'fas fa-circle-notch' => 'circle-notch',
                'fas fa-clipboard' => 'clipboard',
                'far fa-clipboard' => 'clipboard',
                'fas fa-clipboard-check' => 'clipboard-check',
                'fas fa-clipboard-list' => 'clipboard-list',
                'fas fa-clock' => 'clock',
                'far fa-clock' => 'clock',
                'fas fa-clone' => 'clone',
                'far fa-clone' => 'clone',
                'fas fa-closed-captioning' => 'closed-captioning',
                'far fa-closed-captioning' => 'closed-captioning',
                'fas fa-cloud' => 'cloud',
                'fas fa-cloud-download-alt' => 'cloud-download-alt',
                'fas fa-cloud-upload-alt' => 'cloud-upload-alt',
                'fab fa-cloudscale' => 'cloudscale',
                'fab fa-cloudsmith' => 'cloudsmith',
                'fab fa-cloudversify' => 'cloudversify',
                'fas fa-code' => 'code',
                'fas fa-code-branch' => 'code-branch',
                'fab fa-codepen' => 'codepen',
                'fab fa-codiepie' => 'codiepie',
                'fas fa-coffee' => 'coffee',
                'fas fa-cog' => 'cog',
                'fas fa-cogs' => 'cogs',
                'fas fa-columns' => 'columns',
                'fas fa-comment' => 'comment',
                'far fa-comment' => 'comment',
                'fas fa-comment-alt' => 'comment-alt',
                'far fa-comment-alt' => 'comment-alt',
                'fas fa-comment-dots' => 'comment-dots',
                'fas fa-comment-slash' => 'comment-slash',
                'fas fa-comments' => 'comments',
                'far fa-comments' => 'comments',
                'fas fa-compass' => 'compass',
                'far fa-compass' => 'compass',
                'fas fa-compress' => 'compress',
                'fab fa-connectdevelop' => 'connectdevelop',
                'fab fa-contao' => 'contao',
                'fas fa-copy' => 'copy',
                'far fa-copy' => 'copy',
                'fas fa-copyright' => 'copyright',
                'far fa-copyright' => 'copyright',
                'fas fa-couch' => 'couch',
                'fab fa-cpanel' => 'cpanel',
                'fab fa-creative-commons' => 'creative-commons',
                'fas fa-credit-card' => 'credit-card',
                'far fa-credit-card' => 'credit-card',
                'fas fa-crop' => 'crop',
                'fas fa-crosshairs' => 'crosshairs',
                'fab fa-css3' => 'css3',
                'fab fa-css3-alt' => 'css3-alt',
                'fas fa-cube' => 'cube',
                'fas fa-cubes' => 'cubes',
                'fas fa-cut' => 'cut',
                'fab fa-cuttlefish' => 'cuttlefish',
                'fab fa-d-and-d' => 'd-and-d',
                'fab fa-dashcube' => 'dashcube',
                'fas fa-database' => 'database',
                'fas fa-deaf' => 'deaf',
                'fab fa-delicious' => 'delicious',
                'fab fa-deploydog' => 'deploydog',
                'fab fa-deskpro' => 'deskpro',
                'fas fa-desktop' => 'desktop',
                'fab fa-deviantart' => 'deviantart',
                'fas fa-diagnoses' => 'diagnoses',
                'fab fa-digg' => 'digg',
                'fab fa-digital-ocean' => 'digital-ocean',
                'fab fa-discord' => 'discord',
                'fab fa-discourse' => 'discourse',
                'fas fa-dna' => 'dna',
                'fab fa-dochub' => 'dochub',
                'fab fa-docker' => 'docker',
                'fas fa-dollar-sign' => 'dollar-sign',
                'fas fa-dolly' => 'dolly',
                'fas fa-dolly-flatbed' => 'dolly-flatbed',
                'fas fa-donate' => 'donate',
                'fas fa-dot-circle' => 'dot-circle',
                'far fa-dot-circle' => 'dot-circle',
                'fas fa-dove' => 'dove',
                'fas fa-download' => 'download',
                'fab fa-draft2digital' => 'draft2digital',
                'fab fa-dribbble' => 'dribbble',
                'fab fa-dribbble-square' => 'dribbble-square',
                'fab fa-dropbox' => 'dropbox',
                'fab fa-drupal' => 'drupal',
                'fab fa-dyalog' => 'dyalog',
                'fab fa-earlybirds' => 'earlybirds',
                'fab fa-edge' => 'edge',
                'fas fa-edit' => 'edit',
                'far fa-edit' => 'edit',
                'fas fa-eject' => 'eject',
                'fab fa-elementor' => 'elementor',
                'fas fa-ellipsis-h' => 'ellipsis-h',
                'fas fa-ellipsis-v' => 'ellipsis-v',
                'fab fa-ember' => 'ember',
                'fab fa-empire' => 'empire',
                'fas fa-envelope' => 'envelope',
                'far fa-envelope' => 'envelope',
                'fas fa-envelope-open' => 'envelope-open',
                'far fa-envelope-open' => 'envelope-open',
                'fas fa-envelope-square' => 'envelope-square',
                'fab fa-envira' => 'envira',
                'fas fa-eraser' => 'eraser',
                'fab fa-erlang' => 'erlang',
                'fab fa-ethereum' => 'ethereum',
                'fab fa-etsy' => 'etsy',
                'fas fa-euro-sign' => 'euro-sign',
                'fas fa-exchange-alt' => 'exchange-alt',
                'fas fa-exclamation' => 'exclamation',
                'fas fa-exclamation-circle' => 'exclamation-circle',
                'fas fa-exclamation-triangle' => 'exclamation-triangle',
                'fas fa-expand' => 'expand',
                'fas fa-expand-arrows-alt' => 'expand-arrows-alt',
                'fab fa-expeditedssl' => 'expeditedssl',
                'fas fa-external-link-alt' => 'external-link-alt',
                'fas fa-external-link-square-alt' => 'external-link-square-alt',
                'fas fa-eye' => 'eye',
                'fas fa-eye-dropper' => 'eye-dropper',
                'fas fa-eye-slash' => 'eye-slash',
                'far fa-eye-slash' => 'eye-slash',
                'fab fa-facebook' => 'facebook',
                'fab fa-facebook-f' => 'facebook-f',
                'fab fa-facebook-messenger' => 'facebook-messenger',
                'fab fa-facebook-square' => 'facebook-square',
                'fas fa-fast-backward' => 'fast-backward',
                'fas fa-fast-forward' => 'fast-forward',
                'fas fa-fax' => 'fax',
                'fas fa-female' => 'female',
                'fas fa-fighter-jet' => 'fighter-jet',
                'fas fa-file' => 'file',
                'far fa-file' => 'file',
                'fas fa-file-alt' => 'file-alt',
                'far fa-file-alt' => 'file-alt',
                'fas fa-file-archive' => 'file-archive',
                'far fa-file-archive' => 'file-archive',
                'fas fa-file-audio' => 'file-audio',
                'far fa-file-audio' => 'file-audio',
                'fas fa-file-code' => 'file-code',
                'far fa-file-code' => 'file-code',
                'fas fa-file-excel' => 'file-excel',
                'far fa-file-excel' => 'file-excel',
                'fas fa-file-image' => 'file-image',
                'far fa-file-image' => 'file-image',
                'fas fa-file-medical' => 'file-medical',
                'fas fa-file-medical-alt' => 'file-medical-alt',
                'fas fa-file-pdf' => 'file-pdf',
                'far fa-file-pdf' => 'file-pdf',
                'fas fa-file-powerpoint' => 'file-powerpoint',
                'far fa-file-powerpoint' => 'file-powerpoint',
                'fas fa-file-video' => 'file-video',
                'far fa-file-video' => 'file-video',
                'fas fa-file-word' => 'file-word',
                'far fa-file-word' => 'file-word',
                'fas fa-film' => 'film',
                'fas fa-filter' => 'filter',
                'fas fa-fire' => 'fire',
                'fas fa-fire-extinguisher' => 'fire-extinguisher',
                'fab fa-firefox' => 'firefox',
                'fas fa-first-aid' => 'first-aid',
                'fab fa-first-order' => 'first-order',
                'fab fa-firstdraft' => 'firstdraft',
                'fas fa-flag' => 'flag',
                'far fa-flag' => 'flag',
                'fas fa-flag-checkered' => 'flag-checkered',
                'fas fa-flask' => 'flask',
                'fab fa-flickr' => 'flickr',
                'fab fa-flipboard' => 'flipboard',
                'fab fa-fly' => 'fly',
                'fas fa-folder' => 'folder',
                'far fa-folder' => 'folder',
                'fas fa-folder-open' => 'folder-open',
                'far fa-folder-open' => 'folder-open',
                'fas fa-font' => 'font',
                'fab fa-font-awesome' => 'font-awesome',
                'fab fa-font-awesome-alt' => 'font-awesome-alt',
                'fab fa-font-awesome-flag' => 'font-awesome-flag',
                'fab fa-fonticons' => 'fonticons',
                'fab fa-fonticons-fi' => 'fonticons-fi',
                'fas fa-football-ball' => 'football-ball',
                'fab fa-fort-awesome' => 'fort-awesome',
                'fab fa-fort-awesome-alt' => 'fort-awesome-alt',
                'fab fa-forumbee' => 'forumbee',
                'fas fa-forward' => 'forward',
                'fab fa-foursquare' => 'foursquare',
                'fab fa-free-code-camp' => 'free-code-camp',
                'fab fa-freebsd' => 'freebsd',
                'fas fa-frown' => 'frown',
                'far fa-frown' => 'frown',
                'fas fa-futbol' => 'futbol',
                'far fa-futbol' => 'futbol',
                'fas fa-gamepad' => 'gamepad',
                'fas fa-gavel' => 'gavel',
                'fas fa-gem' => 'gem',
                'far fa-gem' => 'gem',
                'fas fa-genderless' => 'genderless',
                'fab fa-get-pocket' => 'get-pocket',
                'fab fa-gg' => 'gg',
                'fab fa-gg-circle' => 'gg-circle',
                'fas fa-gift' => 'gift',
                'fab fa-git' => 'git',
                'fab fa-git-square' => 'git-square',
                'fab fa-github' => 'github',
                'fab fa-github-alt' => 'github-alt',
                'fab fa-github-square' => 'github-square',
                'fab fa-gitkraken' => 'gitkraken',
                'fab fa-gitlab' => 'gitlab',
                'fab fa-gitter' => 'gitter',
                'fas fa-glass-martini' => 'glass-martini',
                'fab fa-glide' => 'glide',
                'fab fa-glide-g' => 'glide-g',
                'fas fa-globe' => 'globe',
                'fab fa-gofore' => 'gofore',
                'fas fa-golf-ball' => 'golf-ball',
                'fab fa-goodreads' => 'goodreads',
                'fab fa-goodreads-g' => 'goodreads-g',
                'fab fa-google' => 'google',
                'fab fa-google-drive' => 'google-drive',
                'fab fa-google-play' => 'google-play',
                'fab fa-google-plus' => 'google-plus',
                'fab fa-google-plus-g' => 'google-plus-g',
                'fab fa-google-plus-square' => 'google-plus-square',
                'fab fa-google-wallet' => 'google-wallet',
                'fas fa-graduation-cap' => 'graduation-cap',
                'fab fa-gratipay' => 'gratipay',
                'fab fa-grav' => 'grav',
                'fab fa-gripfire' => 'gripfire',
                'fab fa-grunt' => 'grunt',
                'fab fa-gulp' => 'gulp',
                'fas fa-h-square' => 'h-square',
                'fab fa-hacker-news' => 'hacker-news',
                'fab fa-hacker-news-square' => 'hacker-news-square',
                'fas fa-hand-holding' => 'hand-holding',
                'fas fa-hand-holding-heart' => 'hand-holding-heart',
                'fas fa-hand-holding-usd' => 'hand-holding-usd',
                'fas fa-hand-lizard' => 'hand-lizard',
                'far fa-hand-lizard' => 'hand-lizard',
                'fas fa-hand-paper' => 'hand-paper',
                'far fa-hand-paper' => 'hand-paper',
                'fas fa-hand-peace' => 'hand-peace',
                'far fa-hand-peace' => 'hand-peace',
                'fas fa-hand-point-down' => 'hand-point-down',
                'far fa-hand-point-down' => 'hand-point-down',
                'fas fa-hand-point-left' => 'hand-point-left',
                'far fa-hand-point-left' => 'hand-point-left',
                'fas fa-hand-point-right' => 'hand-point-right',
                'far fa-hand-point-right' => 'hand-point-right',
                'fas fa-hand-point-up' => 'hand-point-up',
                'far fa-hand-point-up' => 'hand-point-up',
                'fas fa-hand-pointer' => 'hand-pointer',
                'far fa-hand-pointer' => 'hand-pointer',
                'fas fa-hand-rock' => 'hand-rock',
                'far fa-hand-rock' => 'hand-rock',
                'fas fa-hand-scissors' => 'hand-scissors',
                'far fa-hand-scissors' => 'hand-scissors',
                'fas fa-hand-spock' => 'hand-spock',
                'far fa-hand-spock' => 'hand-spock',
                'fas fa-hands' => 'hands',
                'fas fa-hands-helping' => 'hands-helping',
                'fas fa-handshake' => 'handshake',
                'far fa-handshake' => 'handshake',
                'fas fa-hashtag' => 'hashtag',
                'fas fa-hdd' => 'hdd',
                'far fa-hdd' => 'hdd',
                'fas fa-heading' => 'heading',
                'fas fa-headphones' => 'headphones',
                'fas fa-heart' => 'heart',
                'far fa-heart' => 'heart',
                'fas fa-heartbeat' => 'heartbeat',
                'fab fa-hips' => 'hips',
                'fab fa-hire-a-helper' => 'hire-a-helper',
                'fas fa-history' => 'history',
                'fas fa-hockey-puck' => 'hockey-puck',
                'fas fa-home' => 'home',
                'fab fa-hooli' => 'hooli',
                'fas fa-hospital' => 'hospital',
                'far fa-hospital' => 'hospital',
                'fas fa-hospital-alt' => 'hospital-alt',
                'fas fa-hospital-symbol' => 'hospital-symbol',
                'fab fa-hotjar' => 'hotjar',
                'fas fa-hourglass' => 'hourglass',
                'far fa-hourglass' => 'hourglass',
                'fas fa-hourglass-end' => 'hourglass-end',
                'fas fa-hourglass-half' => 'hourglass-half',
                'fas fa-hourglass-start' => 'hourglass-start',
                'fab fa-houzz' => 'houzz',
                'fab fa-html5' => 'html5',
                'fab fa-hubspot' => 'hubspot',
                'fas fa-i-cursor' => 'i-cursor',
                'fas fa-id-badge' => 'id-badge',
                'far fa-id-badge' => 'id-badge',
                'fas fa-id-card' => 'id-card',
                'far fa-id-card' => 'id-card',
                'fas fa-id-card-alt' => 'id-card-alt',
                'fas fa-image' => 'image',
                'far fa-image' => 'image',
                'fas fa-images' => 'images',
                'far fa-images' => 'images',
                'fab fa-imdb' => 'imdb',
                'fas fa-inbox' => 'inbox',
                'fas fa-indent' => 'indent',
                'fas fa-industry' => 'industry',
                'fas fa-info' => 'info',
                'fas fa-info-circle' => 'info-circle',
                'fab fa-instagram' => 'instagram',
                'fab fa-internet-explorer' => 'internet-explorer',
                'fab fa-ioxhost' => 'ioxhost',
                'fas fa-italic' => 'italic',
                'fab fa-itunes' => 'itunes',
                'fab fa-itunes-note' => 'itunes-note',
                'fab fa-java' => 'java',
                'fab fa-jenkins' => 'jenkins',
                'fab fa-joget' => 'joget',
                'fab fa-joomla' => 'joomla',
                'fab fa-js' => 'js',
                'fab fa-js-square' => 'js-square',
                'fab fa-jsfiddle' => 'jsfiddle',
                'fas fa-key' => 'key',
                'fas fa-keyboard' => 'keyboard',
                'far fa-keyboard' => 'keyboard',
                'fab fa-keycdn' => 'keycdn',
                'fab fa-kickstarter' => 'kickstarter',
                'fab fa-kickstarter-k' => 'kickstarter-k',
                'fab fa-korvue' => 'korvue',
                'fas fa-language' => 'language',
                'fas fa-laptop' => 'laptop',
                'fab fa-laravel' => 'laravel',
                'fab fa-lastfm' => 'lastfm',
                'fab fa-lastfm-square' => 'lastfm-square',
                'fas fa-leaf' => 'leaf',
                'fab fa-leanpub' => 'leanpub',
                'fas fa-lemon' => 'lemon',
                'far fa-lemon' => 'lemon',
                'fab fa-less' => 'less',
                'fas fa-level-down-alt' => 'level-down-alt',
                'fas fa-level-up-alt' => 'level-up-alt',
                'fas fa-life-ring' => 'life-ring',
                'far fa-life-ring' => 'life-ring',
                'fas fa-lightbulb' => 'lightbulb',
                'far fa-lightbulb' => 'lightbulb',
                'fab fa-line' => 'line',
                'fas fa-link' => 'link',
                'fab fa-linkedin' => 'linkedin',
                'fab fa-linkedin-in' => 'linkedin-in',
                'fab fa-linode' => 'linode',
                'fab fa-linux' => 'linux',
                'fas fa-lira-sign' => 'lira-sign',
                'fas fa-list' => 'list',
                'fas fa-list-alt' => 'list-alt',
                'far fa-list-alt' => 'list-alt',
                'fas fa-list-ol' => 'list-ol',
                'fas fa-list-ul' => 'list-ul',
                'fas fa-location-arrow' => 'location-arrow',
                'fas fa-lock' => 'lock',
                'fas fa-lock-open' => 'lock-open',
                'fas fa-long-arrow-alt-down' => 'long-arrow-alt-down',
                'fas fa-long-arrow-alt-left' => 'long-arrow-alt-left',
                'fas fa-long-arrow-alt-right' => 'long-arrow-alt-right',
                'fas fa-long-arrow-alt-up' => 'long-arrow-alt-up',
                'fas fa-low-vision' => 'low-vision',
                'fab fa-lyft' => 'lyft',
                'fab fa-magento' => 'magento',
                'fas fa-magic' => 'magic',
                'fas fa-magnet' => 'magnet',
                'fas fa-male' => 'male',
                'fas fa-map' => 'map',
                'far fa-map' => 'map',
                'fas fa-map-marker' => 'map-marker',
                'fas fa-map-marker-alt' => 'map-marker-alt',
                'fas fa-map-pin' => 'map-pin',
                'fas fa-map-signs' => 'map-signs',
                'fas fa-mars' => 'mars',
                'fas fa-mars-double' => 'mars-double',
                'fas fa-mars-stroke' => 'mars-stroke',
                'fas fa-mars-stroke-h' => 'mars-stroke-h',
                'fas fa-mars-stroke-v' => 'mars-stroke-v',
                'fab fa-maxcdn' => 'maxcdn',
                'fab fa-medapps' => 'medapps',
                'fab fa-medium' => 'medium',
                'fab fa-medium-m' => 'medium-m',
                'fas fa-medkit' => 'medkit',
                'fab fa-medrt' => 'medrt',
                'fab fa-meetup' => 'meetup',
                'fas fa-meh' => 'meh',
                'far fa-meh' => 'meh',
                'fas fa-mercury' => 'mercury',
                'fas fa-microchip' => 'microchip',
                'fas fa-microphone' => 'microphone',
                'fas fa-microphone-slash' => 'microphone-slash',
                'fab fa-microsoft' => 'microsoft',
                'fas fa-minus' => 'minus',
                'fas fa-minus-circle' => 'minus-circle',
                'fas fa-minus-square' => 'minus-square',
                'far fa-minus-square' => 'minus-square',
                'fab fa-mix' => 'mix',
                'fab fa-mixcloud' => 'mixcloud',
                'fab fa-mizuni' => 'mizuni',
                'fas fa-mobile' => 'mobile',
                'fas fa-mobile-alt' => 'mobile-alt',
                'fab fa-modx' => 'modx',
                'fab fa-monero' => 'monero',
                'fas fa-money-bill-alt' => 'money-bill-alt',
                'far fa-money-bill-alt' => 'money-bill-alt',
                'fas fa-moon' => 'moon',
                'far fa-moon' => 'moon',
                'fas fa-motorcycle' => 'motorcycle',
                'fas fa-mouse-pointer' => 'mouse-pointer',
                'fas fa-music' => 'music',
                'fab fa-napster' => 'napster',
                'fas fa-neuter' => 'neuter',
                'fas fa-newspaper' => 'newspaper',
                'far fa-newspaper' => 'newspaper',
                'fab fa-nintendo-switch' => 'nintendo-switch',
                'fab fa-node' => 'node',
                'fab fa-node-js' => 'node-js',
                'fas fa-notes-medical' => 'notes-medical',
                'fab fa-npm' => 'npm',
                'fab fa-ns8' => 'ns8',
                'fab fa-nutritionix' => 'nutritionix',
                'fas fa-object-group' => 'object-group',
                'far fa-object-group' => 'object-group',
                'fas fa-object-ungroup' => 'object-ungroup',
                'far fa-object-ungroup' => 'object-ungroup',
                'fab fa-odnoklassniki' => 'odnoklassniki',
                'fab fa-odnoklassniki-square' => 'odnoklassniki-square',
                'fab fa-opencart' => 'opencart',
                'fab fa-openid' => 'openid',
                'fab fa-opera' => 'opera',
                'fab fa-optin-monster' => 'optin-monster',
                'fab fa-osi' => 'osi',
                'fas fa-outdent' => 'outdent',
                'fab fa-page4' => 'page4',
                'fab fa-pagelines' => 'pagelines',
                'fas fa-paint-brush' => 'paint-brush',
                'fab fa-palfed' => 'palfed',
                'fas fa-pallet' => 'pallet',
                'fas fa-paper-plane' => 'paper-plane',
                'far fa-paper-plane' => 'paper-plane',
                'fas fa-paperclip' => 'paperclip',
                'fas fa-parachute-box' => 'parachute-box',
                'fas fa-paragraph' => 'paragraph',
                'fas fa-paste' => 'paste',
                'fab fa-patreon' => 'patreon',
                'fas fa-pause' => 'pause',
                'fas fa-pause-circle' => 'pause-circle',
                'far fa-pause-circle' => 'pause-circle',
                'fas fa-paw' => 'paw',
                'fab fa-paypal' => 'paypal',
                'fas fa-pen-square' => 'pen-square',
                'fas fa-pencil-alt' => 'pencil-alt',
                'fas fa-people-carry' => 'people-carry',
                'fas fa-percent' => 'percent',
                'fab fa-periscope' => 'periscope',
                'fab fa-phabricator' => 'phabricator',
                'fab fa-phoenix-framework' => 'phoenix-framework',
                'fas fa-phone' => 'phone',
                'fas fa-phone-slash' => 'phone-slash',
                'fas fa-phone-square' => 'phone-square',
                'fas fa-phone-volume' => 'phone-volume',
                'fab fa-php' => 'php',
                'fab fa-pied-piper' => 'pied-piper',
                'fab fa-pied-piper-alt' => 'pied-piper-alt',
                'fab fa-pied-piper-hat' => 'pied-piper-hat',
                'fab fa-pied-piper-pp' => 'pied-piper-pp',
                'fas fa-piggy-bank' => 'piggy-bank',
                'fas fa-pills' => 'pills',
                'fab fa-pinterest' => 'pinterest',
                'fab fa-pinterest-p' => 'pinterest-p',
                'fab fa-pinterest-square' => 'pinterest-square',
                'fas fa-plane' => 'plane',
                'fas fa-play' => 'play',
                'fas fa-play-circle' => 'play-circle',
                'far fa-play-circle' => 'play-circle',
                'fab fa-playstation' => 'playstation',
                'fas fa-plug' => 'plug',
                'fas fa-plus' => 'plus',
                'fas fa-plus-circle' => 'plus-circle',
                'fas fa-plus-square' => 'plus-square',
                'far fa-plus-square' => 'plus-square',
                'fas fa-podcast' => 'podcast',
                'fas fa-poo' => 'poo',
                'fas fa-pound-sign' => 'pound-sign',
                'fas fa-power-off' => 'power-off',
                'fas fa-prescription-bottle' => 'prescription-bottle',
                'fas fa-prescription-bottle-alt' => 'prescription-bottle-alt',
                'fas fa-print' => 'print',
                'fas fa-procedures' => 'procedures',
                'fab fa-product-hunt' => 'product-hunt',
                'fab fa-pushed' => 'pushed',
                'fas fa-puzzle-piece' => 'puzzle-piece',
                'fab fa-python' => 'python',
                'fab fa-qq' => 'qq',
                'fas fa-qrcode' => 'qrcode',
                'fas fa-question' => 'question',
                'fas fa-question-circle' => 'question-circle',
                'far fa-question-circle' => 'question-circle',
                'fas fa-quidditch' => 'quidditch',
                'fab fa-quinscape' => 'quinscape',
                'fab fa-quora' => 'quora',
                'fas fa-quote-left' => 'quote-left',
                'fas fa-quote-right' => 'quote-right',
                'fas fa-random' => 'random',
                'fab fa-ravelry' => 'ravelry',
                'fab fa-react' => 'react',
                'fab fa-readme' => 'readme',
                'fab fa-rebel' => 'rebel',
                'fas fa-recycle' => 'recycle',
                'fab fa-red-river' => 'red-river',
                'fab fa-reddit' => 'reddit',
                'fab fa-reddit-alien' => 'reddit-alien',
                'fab fa-reddit-square' => 'reddit-square',
                'fas fa-redo' => 'redo',
                'fas fa-redo-alt' => 'redo-alt',
                'fas fa-registered' => 'registered',
                'far fa-registered' => 'registered',
                'fab fa-rendact' => 'rendact',
                'fab fa-renren' => 'renren',
                'fas fa-reply' => 'reply',
                'fas fa-reply-all' => 'reply-all',
                'fab fa-replyd' => 'replyd',
                'fab fa-resolving' => 'resolving',
                'fas fa-retweet' => 'retweet',
                'fas fa-ribbon' => 'ribbon',
                'fas fa-road' => 'road',
                'fas fa-rocket' => 'rocket',
                'fab fa-rocketchat' => 'rocketchat',
                'fab fa-rockrms' => 'rockrms',
                'fas fa-rss' => 'rss',
                'fas fa-rss-square' => 'rss-square',
                'fas fa-ruble-sign' => 'ruble-sign',
                'fas fa-rupee-sign' => 'rupee-sign',
                'fab fa-safari' => 'safari',
                'fab fa-sass' => 'sass',
                'fas fa-save' => 'save',
                'far fa-save' => 'save',
                'fab fa-schlix' => 'schlix',
                'fab fa-scribd' => 'scribd',
                'fas fa-search' => 'search',
                'fas fa-search-minus' => 'search-minus',
                'fas fa-search-plus' => 'search-plus',
                'fab fa-searchengin' => 'searchengin',
                'fas fa-seedling' => 'seedling',
                'fab fa-sellcast' => 'sellcast',
                'fab fa-sellsy' => 'sellsy',
                'fas fa-server' => 'server',
                'fab fa-servicestack' => 'servicestack',
                'fas fa-share' => 'share',
                'fas fa-share-alt' => 'share-alt',
                'fas fa-share-alt-square' => 'share-alt-square',
                'fas fa-share-square' => 'share-square',
                'far fa-share-square' => 'share-square',
                'fas fa-shekel-sign' => 'shekel-sign',
                'fas fa-shield-alt' => 'shield-alt',
                'fas fa-ship' => 'ship',
                'fas fa-shipping-fast' => 'shipping-fast',
                'fab fa-shirtsinbulk' => 'shirtsinbulk',
                'fas fa-shopping-bag' => 'shopping-bag',
                'fas fa-shopping-basket' => 'shopping-basket',
                'fas fa-shopping-cart' => 'shopping-cart',
                'fas fa-shower' => 'shower',
                'fas fa-sign' => 'sign',
                'fas fa-sign-in-alt' => 'sign-in-alt',
                'fas fa-sign-language' => 'sign-language',
                'fas fa-sign-out-alt' => 'sign-out-alt',
                'fas fa-signal' => 'signal',
                'fab fa-simplybuilt' => 'simplybuilt',
                'fab fa-sistrix' => 'sistrix',
                'fas fa-sitemap' => 'sitemap',
                'fab fa-skyatlas' => 'skyatlas',
                'fab fa-skype' => 'skype',
                'fab fa-slack' => 'slack',
                'fab fa-slack-hash' => 'slack-hash',
                'fas fa-sliders-h' => 'sliders-h',
                'fab fa-slideshare' => 'slideshare',
                'fas fa-smile' => 'smile',
                'far fa-smile' => 'smile',
                'fas fa-smoking' => 'smoking',
                'fab fa-snapchat' => 'snapchat',
                'fab fa-snapchat-ghost' => 'snapchat-ghost',
                'fab fa-snapchat-square' => 'snapchat-square',
                'fas fa-snowflake' => 'snowflake',
                'far fa-snowflake' => 'snowflake',
                'fas fa-sort' => 'sort',
                'fas fa-sort-alpha-down' => 'sort-alpha-down',
                'fas fa-sort-alpha-up' => 'sort-alpha-up',
                'fas fa-sort-amount-down' => 'sort-amount-down',
                'fas fa-sort-amount-up' => 'sort-amount-up',
                'fas fa-sort-down' => 'sort-down',
                'fas fa-sort-numeric-down' => 'sort-numeric-down',
                'fas fa-sort-numeric-up' => 'sort-numeric-up',
                'fas fa-sort-up' => 'sort-up',
                'fab fa-soundcloud' => 'soundcloud',
                'fas fa-space-shuttle' => 'space-shuttle',
                'fab fa-speakap' => 'speakap',
                'fas fa-spinner' => 'spinner',
                'fab fa-spotify' => 'spotify',
                'fas fa-square' => 'square',
                'far fa-square' => 'square',
                'fas fa-square-full' => 'square-full',
                'fab fa-stack-exchange' => 'stack-exchange',
                'fab fa-stack-overflow' => 'stack-overflow',
                'fas fa-star' => 'star',
                'far fa-star' => 'star',
                'fas fa-star-half' => 'star-half',
                'far fa-star-half' => 'star-half',
                'fab fa-staylinked' => 'staylinked',
                'fab fa-steam' => 'steam',
                'fab fa-steam-square' => 'steam-square',
                'fab fa-steam-symbol' => 'steam-symbol',
                'fas fa-step-backward' => 'step-backward',
                'fas fa-step-forward' => 'step-forward',
                'fas fa-stethoscope' => 'stethoscope',
                'fab fa-sticker-mule' => 'sticker-mule',
                'fas fa-sticky-note' => 'sticky-note',
                'far fa-sticky-note' => 'sticky-note',
                'fas fa-stop' => 'stop',
                'fas fa-stop-circle' => 'stop-circle',
                'far fa-stop-circle' => 'stop-circle',
                'fas fa-stopwatch' => 'stopwatch',
                'fab fa-strava' => 'strava',
                'fas fa-street-view' => 'street-view',
                'fas fa-strikethrough' => 'strikethrough',
                'fab fa-stripe' => 'stripe',
                'fab fa-stripe-s' => 'stripe-s',
                'fab fa-studiovinari' => 'studiovinari',
                'fab fa-stumbleupon' => 'stumbleupon',
                'fab fa-stumbleupon-circle' => 'stumbleupon-circle',
                'fas fa-subscript' => 'subscript',
                'fas fa-subway' => 'subway',
                'fas fa-suitcase' => 'suitcase',
                'fas fa-sun' => 'sun',
                'far fa-sun' => 'sun',
                'fab fa-superpowers' => 'superpowers',
                'fas fa-superscript' => 'superscript',
                'fab fa-supple' => 'supple',
                'fas fa-sync' => 'sync',
                'fas fa-sync-alt' => 'sync-alt',
                'fas fa-syringe' => 'syringe',
                'fas fa-table' => 'table',
                'fas fa-table-tennis' => 'table-tennis',
                'fas fa-tablet' => 'tablet',
                'fas fa-tablet-alt' => 'tablet-alt',
                'fas fa-tablets' => 'tablets',
                'fas fa-tachometer-alt' => 'tachometer-alt',
                'fas fa-tag' => 'tag',
                'fas fa-tags' => 'tags',
                'fas fa-tape' => 'tape',
                'fas fa-tasks' => 'tasks',
                'fas fa-taxi' => 'taxi',
                'fab fa-telegram' => 'telegram',
                'fab fa-telegram-plane' => 'telegram-plane',
                'fab fa-tencent-weibo' => 'tencent-weibo',
                'fas fa-terminal' => 'terminal',
                'fas fa-text-height' => 'text-height',
                'fas fa-text-width' => 'text-width',
                'fas fa-th' => 'th',
                'fas fa-th-large' => 'th-large',
                'fas fa-th-list' => 'th-list',
                'fab fa-themeisle' => 'themeisle',
                'fas fa-thermometer' => 'thermometer',
                'fas fa-thermometer-empty' => 'thermometer-empty',
                'fas fa-thermometer-full' => 'thermometer-full',
                'fas fa-thermometer-half' => 'thermometer-half',
                'fas fa-thermometer-quarter' => 'thermometer-quarter',
                'fas fa-thermometer-three-quarters' => 'thermometer-three-quarters',
                'fas fa-thumbs-down' => 'thumbs-down',
                'far fa-thumbs-down' => 'thumbs-down',
                'fas fa-thumbs-up' => 'thumbs-up',
                'far fa-thumbs-up' => 'thumbs-up',
                'fas fa-thumbtack' => 'thumbtack',
                'fas fa-ticket-alt' => 'ticket-alt',
                'fas fa-times' => 'times',
                'fas fa-times-circle' => 'times-circle',
                'far fa-times-circle' => 'times-circle',
                'fas fa-tint' => 'tint',
                'fas fa-toggle-off' => 'toggle-off',
                'fas fa-toggle-on' => 'toggle-on',
                'fas fa-trademark' => 'trademark',
                'fas fa-train' => 'train',
                'fas fa-transgender' => 'transgender',
                'fas fa-transgender-alt' => 'transgender-alt',
                'fas fa-trash' => 'trash',
                'fas fa-trash-alt' => 'trash-alt',
                'far fa-trash-alt' => 'trash-alt',
                'fas fa-tree' => 'tree',
                'fab fa-trello' => 'trello',
                'fab fa-tripadvisor' => 'tripadvisor',
                'fas fa-trophy' => 'trophy',
                'fas fa-truck' => 'truck',
                'fas fa-truck-loading' => 'truck-loading',
                'fas fa-truck-moving' => 'truck-moving',
                'fas fa-tty' => 'tty',
                'fab fa-tumblr' => 'tumblr',
                'fab fa-tumblr-square' => 'tumblr-square',
                'fas fa-tv' => 'tv',
                'fab fa-twitch' => 'twitch',
                'fab fa-twitter' => 'twitter',
                'fab fa-twitter-square' => 'twitter-square',
                'fab fa-typo3' => 'typo3',
                'fab fa-uber' => 'uber',
                'fab fa-uikit' => 'uikit',
                'fas fa-umbrella' => 'umbrella',
                'fas fa-underline' => 'underline',
                'fas fa-undo' => 'undo',
                'fas fa-undo-alt' => 'undo-alt',
                'fab fa-uniregistry' => 'uniregistry',
                'fas fa-universal-access' => 'universal-access',
                'fas fa-university' => 'university',
                'fas fa-unlink' => 'unlink',
                'fas fa-unlock' => 'unlock',
                'fas fa-unlock-alt' => 'unlock-alt',
                'fab fa-untappd' => 'untappd',
                'fas fa-upload' => 'upload',
                'fab fa-usb' => 'usb',
                'fas fa-user' => 'user',
                'far fa-user' => 'user',
                'fas fa-user-circle' => 'user-circle',
                'far fa-user-circle' => 'user-circle',
                'fas fa-user-md' => 'user-md',
                'fas fa-user-plus' => 'user-plus',
                'fas fa-user-secret' => 'user-secret',
                'fas fa-user-times' => 'user-times',
                'fas fa-users' => 'users',
                'fab fa-ussunnah' => 'ussunnah',
                'fas fa-utensil-spoon' => 'utensil-spoon',
                'fas fa-utensils' => 'utensils',
                'fab fa-vaadin' => 'vaadin',
                'fas fa-venus' => 'venus',
                'fas fa-venus-double' => 'venus-double',
                'fas fa-venus-mars' => 'venus-mars',
                'fab fa-viacoin' => 'viacoin',
                'fab fa-viadeo' => 'viadeo',
                'fab fa-viadeo-square' => 'viadeo-square',
                'fas fa-vial' => 'vial',
                'fas fa-vials' => 'vials',
                'fab fa-viber' => 'viber',
                'fas fa-video' => 'video',
                'fas fa-video-slash' => 'video-slash',
                'fab fa-vimeo' => 'vimeo',
                'fab fa-vimeo-square' => 'vimeo-square',
                'fab fa-vimeo-v' => 'vimeo-v',
                'fab fa-vine' => 'vine',
                'fab fa-vk' => 'vk',
                'fab fa-vnv' => 'vnv',
                'fas fa-volleyball-ball' => 'volleyball-ball',
                'fas fa-volume-down' => 'volume-down',
                'fas fa-volume-off' => 'volume-off',
                'fas fa-volume-up' => 'volume-up',
                'fab fa-vuejs' => 'vuejs',
                'fas fa-warehouse' => 'warehouse',
                'fab fa-weibo' => 'weibo',
                'fas fa-weight' => 'weight',
                'fab fa-weixin' => 'weixin',
                'fab fa-whatsapp' => 'whatsapp',
                'fab fa-whatsapp-square' => 'whatsapp-square',
                'fas fa-wheelchair' => 'wheelchair',
                'fab fa-whmcs' => 'whmcs',
                'fas fa-wifi' => 'wifi',
                'fab fa-wikipedia-w' => 'wikipedia-w',
                'fas fa-window-close' => 'window-close',
                'far fa-window-close' => 'window-close',
                'fas fa-window-maximize' => 'window-maximize',
                'far fa-window-maximize' => 'window-maximize',
                'fas fa-window-minimize' => 'window-minimize',
                'far fa-window-minimize' => 'window-minimize',
                'fas fa-window-restore' => 'window-restore',
                'far fa-window-restore' => 'window-restore',
                'fab fa-windows' => 'windows',
                'fas fa-wine-glass' => 'wine-glass',
                'fas fa-won-sign' => 'won-sign',
                'fab fa-wordpress' => 'wordpress',
                'fab fa-wordpress-simple' => 'wordpress-simple',
                'fab fa-wpbeginner' => 'wpbeginner',
                'fab fa-wpexplorer' => 'wpexplorer',
                'fab fa-wpforms' => 'wpforms',
                'fas fa-wrench' => 'wrench',
                'fas fa-x-ray' => 'x-ray',
                'fab fa-xbox' => 'xbox',
                'fab fa-xing' => 'xing',
                'fab fa-xing-square' => 'xing-square',
                'fab fa-y-combinator' => 'y-combinator',
                'fab fa-yahoo' => 'yahoo',
                'fab fa-yandex' => 'yandex',
                'fab fa-yandex-international' => 'yandex-international',
                'fab fa-yelp' => 'yelp',
                'fas fa-yen-sign' => 'yen-sign',
                'fab fa-yoast' => 'yoast',
                'fab fa-youtube' => 'youtube',
                'fab fa-youtube-square' => 'youtube-square',
            );
            return apply_filters( 'FONTAWESOME_ARRAY', $fonts_arr );
        }
    }
    global $wbtmcore;
    $wbtmcore = new FormFieldsGenerator();
}