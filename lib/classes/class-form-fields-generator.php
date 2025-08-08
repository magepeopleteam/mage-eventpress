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
                if(array_key_exists('args',$value)){
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
                'fab fa-500px' => __( '500px', 'mage-eventpress' ),
                'fab fa-accessible-icon' => __( 'accessible-icon', 'mage-eventpress' ),
                'fab fa-accusoft' => __( 'accusoft', 'mage-eventpress' ),
                'fas fa-address-book' => __( 'address-book', 'mage-eventpress' ),
                'far fa-address-book' => __( 'address-book', 'mage-eventpress' ),
                'fas fa-address-card' => __( 'address-card', 'mage-eventpress' ),
                'far fa-address-card' => __( 'address-card', 'mage-eventpress' ),
                'fas fa-adjust' => __( 'adjust', 'mage-eventpress' ),
                'fab fa-adn' => __( 'adn', 'mage-eventpress' ),
                'fab fa-adversal' => __( 'adversal', 'mage-eventpress' ),
                'fab fa-affiliatetheme' => __( 'affiliatetheme', 'mage-eventpress' ),
                'fab fa-algolia' => __( 'algolia', 'mage-eventpress' ),
                'fas fa-align-center' => __( 'align-center', 'mage-eventpress' ),
                'fas fa-align-justify' => __( 'align-justify', 'mage-eventpress' ),
                'fas fa-align-left' => __( 'align-left', 'mage-eventpress' ),
                'fas fa-align-right' => __( 'align-right', 'mage-eventpress' ),
                'fas fa-allergies' => __( 'allergies', 'mage-eventpress' ),
                'fab fa-amazon' => __( 'amazon', 'mage-eventpress' ),
                'fab fa-amazon-pay' => __( 'amazon-pay', 'mage-eventpress' ),
                'fas fa-ambulance' => __( 'ambulance', 'mage-eventpress' ),
                'fas fa-american-sign-language-interpreting' => __( 'american-sign-language-interpreting', 'mage-eventpress' ),
                'fab fa-amilia' => __( 'amilia', 'mage-eventpress' ),
                'fas fa-anchor' => __( 'anchor', 'mage-eventpress' ),
                'fab fa-android' => __( 'android', 'mage-eventpress' ),
                'fab fa-angellist' => __( 'angellist', 'mage-eventpress' ),
                'fas fa-angle-double-down' => __( 'angle-double-down', 'mage-eventpress' ),
                'fas fa-angle-double-left' => __( 'angle-double-left', 'mage-eventpress' ),
                'fas fa-angle-double-right' => __( 'angle-double-right', 'mage-eventpress' ),
                'fas fa-angle-double-up' => __( 'angle-double-up', 'mage-eventpress' ),
                'fas fa-angle-down' => __( 'angle-down', 'mage-eventpress' ),
                'fas fa-angle-left' => __( 'angle-left', 'mage-eventpress' ),
                'fas fa-angle-right' => __( 'angle-right', 'mage-eventpress' ),
                'fas fa-angle-up' => __( 'angle-up', 'mage-eventpress' ),
                'fab fa-angrycreative' => __( 'angrycreative', 'mage-eventpress' ),
                'fab fa-angular' => __( 'angular', 'mage-eventpress' ),
                'fab fa-app-store' => __( 'app-store', 'mage-eventpress' ),
                'fab fa-app-store-ios' => __( 'app-store-ios', 'mage-eventpress' ),
                'fab fa-apper' => __( 'apper', 'mage-eventpress' ),
                'fab fa-apple' => __( 'apple', 'mage-eventpress' ),
                'fab fa-apple-pay' => __( 'apple-pay', 'mage-eventpress' ),
                'fas fa-archive' => __( 'archive', 'mage-eventpress' ),
                'fas fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'mage-eventpress' ),
                'far fa-arrow-alt-circle-down' => __( 'arrow-alt-circle-down', 'mage-eventpress' ),
                'fas fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'mage-eventpress' ),
                'far fa-arrow-alt-circle-left' => __( 'arrow-alt-circle-left', 'mage-eventpress' ),
                'fas fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'mage-eventpress' ),
                'far fa-arrow-alt-circle-right' => __( 'arrow-alt-circle-right', 'mage-eventpress' ),
                'fas fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'mage-eventpress' ),
                'far fa-arrow-alt-circle-up' => __( 'arrow-alt-circle-up', 'mage-eventpress' ),
                'fas fa-arrow-circle-down' => __( 'arrow-circle-down', 'mage-eventpress' ),
                'fas fa-arrow-circle-left' => __( 'arrow-circle-left', 'mage-eventpress' ),
                'fas fa-arrow-circle-right' => __( 'arrow-circle-right', 'mage-eventpress' ),
                'fas fa-arrow-circle-up' => __( 'arrow-circle-up', 'mage-eventpress' ),
                'fas fa-arrow-down' => __( 'arrow-down', 'mage-eventpress' ),
                'fas fa-arrow-left' => __( 'arrow-left', 'mage-eventpress' ),
                'fas fa-arrow-right' => __( 'arrow-right', 'mage-eventpress' ),
                'fas fa-arrow-up' => __( 'arrow-up', 'mage-eventpress' ),
                'fas fa-arrows-alt' => __( 'arrows-alt', 'mage-eventpress' ),
                'fas fa-arrows-alt-h' => __( 'arrows-alt-h', 'mage-eventpress' ),
                'fas fa-arrows-alt-v' => __( 'arrows-alt-v', 'mage-eventpress' ),
                'fas fa-assistive-listening-systems' => __( 'assistive-listening-systems', 'mage-eventpress' ),
                'fas fa-asterisk' => __( 'asterisk', 'mage-eventpress' ),
                'fab fa-asymmetrik' => __( 'asymmetrik', 'mage-eventpress' ),
                'fas fa-at' => __( 'at', 'mage-eventpress' ),
                'fab fa-audible' => __( 'audible', 'mage-eventpress' ),
                'fas fa-audio-description' => __( 'audio-description', 'mage-eventpress' ),
                'fab fa-autoprefixer' => __( 'autoprefixer', 'mage-eventpress' ),
                'fab fa-avianex' => __( 'avianex', 'mage-eventpress' ),
                'fab fa-aviato' => __( 'aviato', 'mage-eventpress' ),
                'fab fa-aws' => __( 'aws', 'mage-eventpress' ),
                'fas fa-backward' => __( 'backward', 'mage-eventpress' ),
                'fas fa-balance-scale' => __( 'balance-scale', 'mage-eventpress' ),
                'fas fa-ban' => __( 'ban', 'mage-eventpress' ),
                'fas fa-band-aid' => __( 'band-aid', 'mage-eventpress' ),
                'fab fa-bandcamp' => __( 'bandcamp', 'mage-eventpress' ),
                'fas fa-barcode' => __( 'barcode', 'mage-eventpress' ),
                'fas fa-bars' => __( 'bars', 'mage-eventpress' ),
                'fas fa-baseball-ball' => __( 'baseball-ball', 'mage-eventpress' ),
                'fas fa-basketball-ball' => __( 'basketball-ball', 'mage-eventpress' ),
                'fas fa-bath' => __( 'bath', 'mage-eventpress' ),
                'fas fa-battery-empty' => __( 'battery-empty', 'mage-eventpress' ),
                'fas fa-battery-full' => __( 'battery-full', 'mage-eventpress' ),
                'fas fa-battery-half' => __( 'battery-half', 'mage-eventpress' ),
                'fas fa-battery-quarter' => __( 'battery-quarter', 'mage-eventpress' ),
                'fas fa-battery-three-quarters' => __( 'battery-three-quarters', 'mage-eventpress' ),
                'fas fa-bed' => __( 'bed', 'mage-eventpress' ),
                'fas fa-beer' => __( 'beer', 'mage-eventpress' ),
                'fab fa-behance' => __( 'behance', 'mage-eventpress' ),
                'fab fa-behance-square' => __( 'behance-square', 'mage-eventpress' ),
                'fas fa-bell' => __( 'bell', 'mage-eventpress' ),
                'far fa-bell' => __( 'bell', 'mage-eventpress' ),
                'fas fa-bell-slash' => __( 'bell-slash', 'mage-eventpress' ),
                'far fa-bell-slash' => __( 'bell-slash', 'mage-eventpress' ),
                'fas fa-bicycle' => __( 'bicycle', 'mage-eventpress' ),
                'fab fa-bimobject' => __( 'bimobject', 'mage-eventpress' ),
                'fas fa-binoculars' => __( 'binoculars', 'mage-eventpress' ),
                'fas fa-birthday-cake' => __( 'birthday-cake', 'mage-eventpress' ),
                'fab fa-bitbucket' => __( 'bitbucket', 'mage-eventpress' ),
                'fab fa-bitcoin' => __( 'bitcoin', 'mage-eventpress' ),
                'fab fa-bity' => __( 'bity', 'mage-eventpress' ),
                'fab fa-black-tie' => __( 'black-tie', 'mage-eventpress' ),
                'fab fa-blackberry' => __( 'blackberry', 'mage-eventpress' ),
                'fas fa-blind' => __( 'blind', 'mage-eventpress' ),
                'fab fa-blogger' => __( 'blogger', 'mage-eventpress' ),
                'fab fa-blogger-b' => __( 'blogger-b', 'mage-eventpress' ),
                'fab fa-bluetooth' => __( 'bluetooth', 'mage-eventpress' ),
                'fab fa-bluetooth-b' => __( 'bluetooth-b', 'mage-eventpress' ),
                'fas fa-bold' => __( 'bold', 'mage-eventpress' ),
                'fas fa-bolt' => __( 'bolt', 'mage-eventpress' ),
                'fas fa-bomb' => __( 'bomb', 'mage-eventpress' ),
                'fas fa-book' => __( 'book', 'mage-eventpress' ),
                'fas fa-bookmark' => __( 'bookmark', 'mage-eventpress' ),
                'far fa-bookmark' => __( 'bookmark', 'mage-eventpress' ),
                'fas fa-bowling-ball' => __( 'bowling-ball', 'mage-eventpress' ),
                'fas fa-box' => __( 'box', 'mage-eventpress' ),
                'fas fa-box-open' => __( 'box-open', 'mage-eventpress' ),
                'fas fa-boxes' => __( 'boxes', 'mage-eventpress' ),
                'fas fa-braille' => __( 'braille', 'mage-eventpress' ),
                'fas fa-briefcase' => __( 'briefcase', 'mage-eventpress' ),
                'fas fa-briefcase-medical' => __( 'briefcase-medical', 'mage-eventpress' ),
                'fab fa-btc' => __( 'btc', 'mage-eventpress' ),
                'fas fa-bug' => __( 'bug', 'mage-eventpress' ),
                'fas fa-building' => __( 'building', 'mage-eventpress' ),
                'far fa-building' => __( 'building', 'mage-eventpress' ),
                'fas fa-bullhorn' => __( 'bullhorn', 'mage-eventpress' ),
                'fas fa-bullseye' => __( 'bullseye', 'mage-eventpress' ),
                'fas fa-burn' => __( 'burn', 'mage-eventpress' ),
                'fab fa-buromobelexperte' => __( 'buromobelexperte', 'mage-eventpress' ),
                'fas fa-bus' => __( 'bus', 'mage-eventpress' ),
                'fab fa-buysellads' => __( 'buysellads', 'mage-eventpress' ),
                'fas fa-calculator' => __( 'calculator', 'mage-eventpress' ),
                'fas fa-calendar' => __( 'calendar', 'mage-eventpress' ),
                'far fa-calendar' => __( 'calendar', 'mage-eventpress' ),
                'fas fa-calendar-alt' => __( 'calendar-alt', 'mage-eventpress' ),
                'far fa-calendar-alt' => __( 'calendar-alt', 'mage-eventpress' ),
                'fas fa-calendar-check' => __( 'calendar-check', 'mage-eventpress' ),
                'far fa-calendar-check' => __( 'calendar-check', 'mage-eventpress' ),
                'fas fa-calendar-minus' => __( 'calendar-minus', 'mage-eventpress' ),
                'far fa-calendar-minus' => __( 'calendar-minus', 'mage-eventpress' ),
                'fas fa-calendar-plus' => __( 'calendar-plus', 'mage-eventpress' ),
                'far fa-calendar-plus' => __( 'calendar-plus', 'mage-eventpress' ),
                'fas fa-calendar-times' => __( 'calendar-times', 'mage-eventpress' ),
                'far fa-calendar-times' => __( 'calendar-times', 'mage-eventpress' ),
                'fas fa-camera' => __( 'camera', 'mage-eventpress' ),
                'fas fa-camera-retro' => __( 'camera-retro', 'mage-eventpress' ),
                'fas fa-capsules' => __( 'capsules', 'mage-eventpress' ),
                'fas fa-car' => __( 'car', 'mage-eventpress' ),
                'fas fa-caret-down' => __( 'caret-down', 'mage-eventpress' ),
                'fas fa-caret-left' => __( 'caret-left', 'mage-eventpress' ),
                'fas fa-caret-right' => __( 'caret-right', 'mage-eventpress' ),
                'fas fa-caret-square-down' => __( 'caret-square-down', 'mage-eventpress' ),
                'far fa-caret-square-down' => __( 'caret-square-down', 'mage-eventpress' ),
                'fas fa-caret-square-left' => __( 'caret-square-left', 'mage-eventpress' ),
                'far fa-caret-square-left' => __( 'caret-square-left', 'mage-eventpress' ),
                'fas fa-caret-square-right' => __( 'caret-square-right', 'mage-eventpress' ),
                'far fa-caret-square-right' => __( 'caret-square-right', 'mage-eventpress' ),
                'fas fa-caret-square-up' => __( 'caret-square-up', 'mage-eventpress' ),
                'far fa-caret-square-up' => __( 'caret-square-up', 'mage-eventpress' ),
                'fas fa-caret-up' => __( 'caret-up', 'mage-eventpress' ),
                'fas fa-cart-arrow-down' => __( 'cart-arrow-down', 'mage-eventpress' ),
                'fas fa-cart-plus' => __( 'cart-plus', 'mage-eventpress' ),
                'fab fa-cc-amazon-pay' => __( 'cc-amazon-pay', 'mage-eventpress' ),
                'fab fa-cc-amex' => __( 'cc-amex', 'mage-eventpress' ),
                'fab fa-cc-apple-pay' => __( 'cc-apple-pay', 'mage-eventpress' ),
                'fab fa-cc-diners-club' => __( 'cc-diners-club', 'mage-eventpress' ),
                'fab fa-cc-discover' => __( 'cc-discover', 'mage-eventpress' ),
                'fab fa-cc-jcb' => __( 'cc-jcb', 'mage-eventpress' ),
                'fab fa-cc-mastercard' => __( 'cc-mastercard', 'mage-eventpress' ),
                'fab fa-cc-paypal' => __( 'cc-paypal', 'mage-eventpress' ),
                'fab fa-cc-stripe' => __( 'cc-stripe', 'mage-eventpress' ),
                'fab fa-cc-visa' => __( 'cc-visa', 'mage-eventpress' ),
                'fab fa-centercode' => __( 'centercode', 'mage-eventpress' ),
                'fas fa-certificate' => __( 'certificate', 'mage-eventpress' ),
                'fas fa-chart-area' => __( 'chart-area', 'mage-eventpress' ),
                'fas fa-chart-bar' => __( 'chart-bar', 'mage-eventpress' ),
                'far fa-chart-bar' => __( 'chart-bar', 'mage-eventpress' ),
                'fas fa-chart-line' => __( 'chart-line', 'mage-eventpress' ),
                'fas fa-chart-pie' => __( 'chart-pie', 'mage-eventpress' ),
                'fas fa-check' => __( 'check', 'mage-eventpress' ),
                'fas fa-check-circle' => __( 'check-circle', 'mage-eventpress' ),
                'far fa-check-circle' => __( 'check-circle', 'mage-eventpress' ),
                'fas fa-check-square' => __( 'check-square', 'mage-eventpress' ),
                'far fa-check-square' => __( 'check-square', 'mage-eventpress' ),
                'fas fa-chess' => __( 'chess', 'mage-eventpress' ),
                'fas fa-chess-bishop' => __( 'chess-bishop', 'mage-eventpress' ),
                'fas fa-chess-board' => __( 'chess-board', 'mage-eventpress' ),
                'fas fa-chess-king' => __( 'chess-king', 'mage-eventpress' ),
                'fas fa-chess-knight' => __( 'chess-knight', 'mage-eventpress' ),
                'fas fa-chess-pawn' => __( 'chess-pawn', 'mage-eventpress' ),
                'fas fa-chess-queen' => __( 'chess-queen', 'mage-eventpress' ),
                'fas fa-chess-rook' => __( 'chess-rook', 'mage-eventpress' ),
                'fas fa-chevron-circle-down' => __( 'chevron-circle-down', 'mage-eventpress' ),
                'fas fa-chevron-circle-left' => __( 'chevron-circle-left', 'mage-eventpress' ),
                'fas fa-chevron-circle-right' => __( 'chevron-circle-right', 'mage-eventpress' ),
                'fas fa-chevron-circle-up' => __( 'chevron-circle-up', 'mage-eventpress' ),
                'fas fa-chevron-down' => __( 'chevron-down', 'mage-eventpress' ),
                'fas fa-chevron-left' => __( 'chevron-left', 'mage-eventpress' ),
                'fas fa-chevron-right' => __( 'chevron-right', 'mage-eventpress' ),
                'fas fa-chevron-up' => __( 'chevron-up', 'mage-eventpress' ),
                'fas fa-child' => __( 'child', 'mage-eventpress' ),
                'fab fa-chrome' => __( 'chrome', 'mage-eventpress' ),
                'fas fa-circle' => __( 'circle', 'mage-eventpress' ),
                'far fa-circle' => __( 'circle', 'mage-eventpress' ),
                'fas fa-circle-notch' => __( 'circle-notch', 'mage-eventpress' ),
                'fas fa-clipboard' => __( 'clipboard', 'mage-eventpress' ),
                'far fa-clipboard' => __( 'clipboard', 'mage-eventpress' ),
                'fas fa-clipboard-check' => __( 'clipboard-check', 'mage-eventpress' ),
                'fas fa-clipboard-list' => __( 'clipboard-list', 'mage-eventpress' ),
                'fas fa-clock' => __( 'clock', 'mage-eventpress' ),
                'far fa-clock' => __( 'clock', 'mage-eventpress' ),
                'fas fa-clone' => __( 'clone', 'mage-eventpress' ),
                'far fa-clone' => __( 'clone', 'mage-eventpress' ),
                'fas fa-closed-captioning' => __( 'closed-captioning', 'mage-eventpress' ),
                'far fa-closed-captioning' => __( 'closed-captioning', 'mage-eventpress' ),
                'fas fa-cloud' => __( 'cloud', 'mage-eventpress' ),
                'fas fa-cloud-download-alt' => __( 'cloud-download-alt', 'mage-eventpress' ),
                'fas fa-cloud-upload-alt' => __( 'cloud-upload-alt', 'mage-eventpress' ),
                'fab fa-cloudscale' => __( 'cloudscale', 'mage-eventpress' ),
                'fab fa-cloudsmith' => __( 'cloudsmith', 'mage-eventpress' ),
                'fab fa-cloudversify' => __( 'cloudversify', 'mage-eventpress' ),
                'fas fa-code' => __( 'code', 'mage-eventpress' ),
                'fas fa-code-branch' => __( 'code-branch', 'mage-eventpress' ),
                'fab fa-codepen' => __( 'codepen', 'mage-eventpress' ),
                'fab fa-codiepie' => __( 'codiepie', 'mage-eventpress' ),
                'fas fa-coffee' => __( 'coffee', 'mage-eventpress' ),
                'fas fa-cog' => __( 'cog', 'mage-eventpress' ),
                'fas fa-cogs' => __( 'cogs', 'mage-eventpress' ),
                'fas fa-columns' => __( 'columns', 'mage-eventpress' ),
                'fas fa-comment' => __( 'comment', 'mage-eventpress' ),
                'far fa-comment' => __( 'comment', 'mage-eventpress' ),
                'fas fa-comment-alt' => __( 'comment-alt', 'mage-eventpress' ),
                'far fa-comment-alt' => __( 'comment-alt', 'mage-eventpress' ),
                'fas fa-comment-dots' => __( 'comment-dots', 'mage-eventpress' ),
                'fas fa-comment-slash' => __( 'comment-slash', 'mage-eventpress' ),
                'fas fa-comments' => __( 'comments', 'mage-eventpress' ),
                'far fa-comments' => __( 'comments', 'mage-eventpress' ),
                'fas fa-compass' => __( 'compass', 'mage-eventpress' ),
                'far fa-compass' => __( 'compass', 'mage-eventpress' ),
                'fas fa-compress' => __( 'compress', 'mage-eventpress' ),
                'fab fa-connectdevelop' => __( 'connectdevelop', 'mage-eventpress' ),
                'fab fa-contao' => __( 'contao', 'mage-eventpress' ),
                'fas fa-copy' => __( 'copy', 'mage-eventpress' ),
                'far fa-copy' => __( 'copy', 'mage-eventpress' ),
                'fas fa-copyright' => __( 'copyright', 'mage-eventpress' ),
                'far fa-copyright' => __( 'copyright', 'mage-eventpress' ),
                'fas fa-couch' => __( 'couch', 'mage-eventpress' ),
                'fab fa-cpanel' => __( 'cpanel', 'mage-eventpress' ),
                'fab fa-creative-commons' => __( 'creative-commons', 'mage-eventpress' ),
                'fas fa-credit-card' => __( 'credit-card', 'mage-eventpress' ),
                'far fa-credit-card' => __( 'credit-card', 'mage-eventpress' ),
                'fas fa-crop' => __( 'crop', 'mage-eventpress' ),
                'fas fa-crosshairs' => __( 'crosshairs', 'mage-eventpress' ),
                'fab fa-css3' => __( 'css3', 'mage-eventpress' ),
                'fab fa-css3-alt' => __( 'css3-alt', 'mage-eventpress' ),
                'fas fa-cube' => __( 'cube', 'mage-eventpress' ),
                'fas fa-cubes' => __( 'cubes', 'mage-eventpress' ),
                'fas fa-cut' => __( 'cut', 'mage-eventpress' ),
                'fab fa-cuttlefish' => __( 'cuttlefish', 'mage-eventpress' ),
                'fab fa-d-and-d' => __( 'd-and-d', 'mage-eventpress' ),
                'fab fa-dashcube' => __( 'dashcube', 'mage-eventpress' ),
                'fas fa-database' => __( 'database', 'mage-eventpress' ),
                'fas fa-deaf' => __( 'deaf', 'mage-eventpress' ),
                'fab fa-delicious' => __( 'delicious', 'mage-eventpress' ),
                'fab fa-deploydog' => __( 'deploydog', 'mage-eventpress' ),
                'fab fa-deskpro' => __( 'deskpro', 'mage-eventpress' ),
                'fas fa-desktop' => __( 'desktop', 'mage-eventpress' ),
                'fab fa-deviantart' => __( 'deviantart', 'mage-eventpress' ),
                'fas fa-diagnoses' => __( 'diagnoses', 'mage-eventpress' ),
                'fab fa-digg' => __( 'digg', 'mage-eventpress' ),
                'fab fa-digital-ocean' => __( 'digital-ocean', 'mage-eventpress' ),
                'fab fa-discord' => __( 'discord', 'mage-eventpress' ),
                'fab fa-discourse' => __( 'discourse', 'mage-eventpress' ),
                'fas fa-dna' => __( 'dna', 'mage-eventpress' ),
                'fab fa-dochub' => __( 'dochub', 'mage-eventpress' ),
                'fab fa-docker' => __( 'docker', 'mage-eventpress' ),
                'fas fa-dollar-sign' => __( 'dollar-sign', 'mage-eventpress' ),
                'fas fa-dolly' => __( 'dolly', 'mage-eventpress' ),
                'fas fa-dolly-flatbed' => __( 'dolly-flatbed', 'mage-eventpress' ),
                'fas fa-donate' => __( 'donate', 'mage-eventpress' ),
                'fas fa-dot-circle' => __( 'dot-circle', 'mage-eventpress' ),
                'far fa-dot-circle' => __( 'dot-circle', 'mage-eventpress' ),
                'fas fa-dove' => __( 'dove', 'mage-eventpress' ),
                'fas fa-download' => __( 'download', 'mage-eventpress' ),
                'fab fa-draft2digital' => __( 'draft2digital', 'mage-eventpress' ),
                'fab fa-dribbble' => __( 'dribbble', 'mage-eventpress' ),
                'fab fa-dribbble-square' => __( 'dribbble-square', 'mage-eventpress' ),
                'fab fa-dropbox' => __( 'dropbox', 'mage-eventpress' ),
                'fab fa-drupal' => __( 'drupal', 'mage-eventpress' ),
                'fab fa-dyalog' => __( 'dyalog', 'mage-eventpress' ),
                'fab fa-earlybirds' => __( 'earlybirds', 'mage-eventpress' ),
                'fab fa-edge' => __( 'edge', 'mage-eventpress' ),
                'fas fa-edit' => __( 'edit', 'mage-eventpress' ),
                'far fa-edit' => __( 'edit', 'mage-eventpress' ),
                'fas fa-eject' => __( 'eject', 'mage-eventpress' ),
                'fab fa-elementor' => __( 'elementor', 'mage-eventpress' ),
                'fas fa-ellipsis-h' => __( 'ellipsis-h', 'mage-eventpress' ),
                'fas fa-ellipsis-v' => __( 'ellipsis-v', 'mage-eventpress' ),
                'fab fa-ember' => __( 'ember', 'mage-eventpress' ),
                'fab fa-empire' => __( 'empire', 'mage-eventpress' ),
                'fas fa-envelope' => __( 'envelope', 'mage-eventpress' ),
                'far fa-envelope' => __( 'envelope', 'mage-eventpress' ),
                'fas fa-envelope-open' => __( 'envelope-open', 'mage-eventpress' ),
                'far fa-envelope-open' => __( 'envelope-open', 'mage-eventpress' ),
                'fas fa-envelope-square' => __( 'envelope-square', 'mage-eventpress' ),
                'fab fa-envira' => __( 'envira', 'mage-eventpress' ),
                'fas fa-eraser' => __( 'eraser', 'mage-eventpress' ),
                'fab fa-erlang' => __( 'erlang', 'mage-eventpress' ),
                'fab fa-ethereum' => __( 'ethereum', 'mage-eventpress' ),
                'fab fa-etsy' => __( 'etsy', 'mage-eventpress' ),
                'fas fa-euro-sign' => __( 'euro-sign', 'mage-eventpress' ),
                'fas fa-exchange-alt' => __( 'exchange-alt', 'mage-eventpress' ),
                'fas fa-exclamation' => __( 'exclamation', 'mage-eventpress' ),
                'fas fa-exclamation-circle' => __( 'exclamation-circle', 'mage-eventpress' ),
                'fas fa-exclamation-triangle' => __( 'exclamation-triangle', 'mage-eventpress' ),
                'fas fa-expand' => __( 'expand', 'mage-eventpress' ),
                'fas fa-expand-arrows-alt' => __( 'expand-arrows-alt', 'mage-eventpress' ),
                'fab fa-expeditedssl' => __( 'expeditedssl', 'mage-eventpress' ),
                'fas fa-external-link-alt' => __( 'external-link-alt', 'mage-eventpress' ),
                'fas fa-external-link-square-alt' => __( 'external-link-square-alt', 'mage-eventpress' ),
                'fas fa-eye' => __( 'eye', 'mage-eventpress' ),
                'fas fa-eye-dropper' => __( 'eye-dropper', 'mage-eventpress' ),
                'fas fa-eye-slash' => __( 'eye-slash', 'mage-eventpress' ),
                'far fa-eye-slash' => __( 'eye-slash', 'mage-eventpress' ),
                'fab fa-facebook' => __( 'facebook', 'mage-eventpress' ),
                'fab fa-facebook-f' => __( 'facebook-f', 'mage-eventpress' ),
                'fab fa-facebook-messenger' => __( 'facebook-messenger', 'mage-eventpress' ),
                'fab fa-facebook-square' => __( 'facebook-square', 'mage-eventpress' ),
                'fas fa-fast-backward' => __( 'fast-backward', 'mage-eventpress' ),
                'fas fa-fast-forward' => __( 'fast-forward', 'mage-eventpress' ),
                'fas fa-fax' => __( 'fax', 'mage-eventpress' ),
                'fas fa-female' => __( 'female', 'mage-eventpress' ),
                'fas fa-fighter-jet' => __( 'fighter-jet', 'mage-eventpress' ),
                'fas fa-file' => __( 'file', 'mage-eventpress' ),
                'far fa-file' => __( 'file', 'mage-eventpress' ),
                'fas fa-file-alt' => __( 'file-alt', 'mage-eventpress' ),
                'far fa-file-alt' => __( 'file-alt', 'mage-eventpress' ),
                'fas fa-file-archive' => __( 'file-archive', 'mage-eventpress' ),
                'far fa-file-archive' => __( 'file-archive', 'mage-eventpress' ),
                'fas fa-file-audio' => __( 'file-audio', 'mage-eventpress' ),
                'far fa-file-audio' => __( 'file-audio', 'mage-eventpress' ),
                'fas fa-file-code' => __( 'file-code', 'mage-eventpress' ),
                'far fa-file-code' => __( 'file-code', 'mage-eventpress' ),
                'fas fa-file-excel' => __( 'file-excel', 'mage-eventpress' ),
                'far fa-file-excel' => __( 'file-excel', 'mage-eventpress' ),
                'fas fa-file-image' => __( 'file-image', 'mage-eventpress' ),
                'far fa-file-image' => __( 'file-image', 'mage-eventpress' ),
                'fas fa-file-medical' => __( 'file-medical', 'mage-eventpress' ),
                'fas fa-file-medical-alt' => __( 'file-medical-alt', 'mage-eventpress' ),
                'fas fa-file-pdf' => __( 'file-pdf', 'mage-eventpress' ),
                'far fa-file-pdf' => __( 'file-pdf', 'mage-eventpress' ),
                'fas fa-file-powerpoint' => __( 'file-powerpoint', 'mage-eventpress' ),
                'far fa-file-powerpoint' => __( 'file-powerpoint', 'mage-eventpress' ),
                'fas fa-file-video' => __( 'file-video', 'mage-eventpress' ),
                'far fa-file-video' => __( 'file-video', 'mage-eventpress' ),
                'fas fa-file-word' => __( 'file-word', 'mage-eventpress' ),
                'far fa-file-word' => __( 'file-word', 'mage-eventpress' ),
                'fas fa-film' => __( 'film', 'mage-eventpress' ),
                'fas fa-filter' => __( 'filter', 'mage-eventpress' ),
                'fas fa-fire' => __( 'fire', 'mage-eventpress' ),
                'fas fa-fire-extinguisher' => __( 'fire-extinguisher', 'mage-eventpress' ),
                'fab fa-firefox' => __( 'firefox', 'mage-eventpress' ),
                'fas fa-first-aid' => __( 'first-aid', 'mage-eventpress' ),
                'fab fa-first-order' => __( 'first-order', 'mage-eventpress' ),
                'fab fa-firstdraft' => __( 'firstdraft', 'mage-eventpress' ),
                'fas fa-flag' => __( 'flag', 'mage-eventpress' ),
                'far fa-flag' => __( 'flag', 'mage-eventpress' ),
                'fas fa-flag-checkered' => __( 'flag-checkered', 'mage-eventpress' ),
                'fas fa-flask' => __( 'flask', 'mage-eventpress' ),
                'fab fa-flickr' => __( 'flickr', 'mage-eventpress' ),
                'fab fa-flipboard' => __( 'flipboard', 'mage-eventpress' ),
                'fab fa-fly' => __( 'fly', 'mage-eventpress' ),
                'fas fa-folder' => __( 'folder', 'mage-eventpress' ),
                'far fa-folder' => __( 'folder', 'mage-eventpress' ),
                'fas fa-folder-open' => __( 'folder-open', 'mage-eventpress' ),
                'far fa-folder-open' => __( 'folder-open', 'mage-eventpress' ),
                'fas fa-font' => __( 'font', 'mage-eventpress' ),
                'fab fa-font-awesome' => __( 'font-awesome', 'mage-eventpress' ),
                'fab fa-font-awesome-alt' => __( 'font-awesome-alt', 'mage-eventpress' ),
                'fab fa-font-awesome-flag' => __( 'font-awesome-flag', 'mage-eventpress' ),
                'fab fa-fonticons' => __( 'fonticons', 'mage-eventpress' ),
                'fab fa-fonticons-fi' => __( 'fonticons-fi', 'mage-eventpress' ),
                'fas fa-football-ball' => __( 'football-ball', 'mage-eventpress' ),
                'fab fa-fort-awesome' => __( 'fort-awesome', 'mage-eventpress' ),
                'fab fa-fort-awesome-alt' => __( 'fort-awesome-alt', 'mage-eventpress' ),
                'fab fa-forumbee' => __( 'forumbee', 'mage-eventpress' ),
                'fas fa-forward' => __( 'forward', 'mage-eventpress' ),
                'fab fa-foursquare' => __( 'foursquare', 'mage-eventpress' ),
                'fab fa-free-code-camp' => __( 'free-code-camp', 'mage-eventpress' ),
                'fab fa-freebsd' => __( 'freebsd', 'mage-eventpress' ),
                'fas fa-frown' => __( 'frown', 'mage-eventpress' ),
                'far fa-frown' => __( 'frown', 'mage-eventpress' ),
                'fas fa-futbol' => __( 'futbol', 'mage-eventpress' ),
                'far fa-futbol' => __( 'futbol', 'mage-eventpress' ),
                'fas fa-gamepad' => __( 'gamepad', 'mage-eventpress' ),
                'fas fa-gavel' => __( 'gavel', 'mage-eventpress' ),
                'fas fa-gem' => __( 'gem', 'mage-eventpress' ),
                'far fa-gem' => __( 'gem', 'mage-eventpress' ),
                'fas fa-genderless' => __( 'genderless', 'mage-eventpress' ),
                'fab fa-get-pocket' => __( 'get-pocket', 'mage-eventpress' ),
                'fab fa-gg' => __( 'gg', 'mage-eventpress' ),
                'fab fa-gg-circle' => __( 'gg-circle', 'mage-eventpress' ),
                'fas fa-gift' => __( 'gift', 'mage-eventpress' ),
                'fab fa-git' => __( 'git', 'mage-eventpress' ),
                'fab fa-git-square' => __( 'git-square', 'mage-eventpress' ),
                'fab fa-github' => __( 'github', 'mage-eventpress' ),
                'fab fa-github-alt' => __( 'github-alt', 'mage-eventpress' ),
                'fab fa-github-square' => __( 'github-square', 'mage-eventpress' ),
                'fab fa-gitkraken' => __( 'gitkraken', 'mage-eventpress' ),
                'fab fa-gitlab' => __( 'gitlab', 'mage-eventpress' ),
                'fab fa-gitter' => __( 'gitter', 'mage-eventpress' ),
                'fas fa-glass-martini' => __( 'glass-martini', 'mage-eventpress' ),
                'fab fa-glide' => __( 'glide', 'mage-eventpress' ),
                'fab fa-glide-g' => __( 'glide-g', 'mage-eventpress' ),
                'fas fa-globe' => __( 'globe', 'mage-eventpress' ),
                'fab fa-gofore' => __( 'gofore', 'mage-eventpress' ),
                'fas fa-golf-ball' => __( 'golf-ball', 'mage-eventpress' ),
                'fab fa-goodreads' => __( 'goodreads', 'mage-eventpress' ),
                'fab fa-goodreads-g' => __( 'goodreads-g', 'mage-eventpress' ),
                'fab fa-google' => __( 'google', 'mage-eventpress' ),
                'fab fa-google-drive' => __( 'google-drive', 'mage-eventpress' ),
                'fab fa-google-play' => __( 'google-play', 'mage-eventpress' ),
                'fab fa-google-plus' => __( 'google-plus', 'mage-eventpress' ),
                'fab fa-google-plus-g' => __( 'google-plus-g', 'mage-eventpress' ),
                'fab fa-google-plus-square' => __( 'google-plus-square', 'mage-eventpress' ),
                'fab fa-google-wallet' => __( 'google-wallet', 'mage-eventpress' ),
                'fas fa-graduation-cap' => __( 'graduation-cap', 'mage-eventpress' ),
                'fab fa-gratipay' => __( 'gratipay', 'mage-eventpress' ),
                'fab fa-grav' => __( 'grav', 'mage-eventpress' ),
                'fab fa-gripfire' => __( 'gripfire', 'mage-eventpress' ),
                'fab fa-grunt' => __( 'grunt', 'mage-eventpress' ),
                'fab fa-gulp' => __( 'gulp', 'mage-eventpress' ),
                'fas fa-h-square' => __( 'h-square', 'mage-eventpress' ),
                'fab fa-hacker-news' => __( 'hacker-news', 'mage-eventpress' ),
                'fab fa-hacker-news-square' => __( 'hacker-news-square', 'mage-eventpress' ),
                'fas fa-hand-holding' => __( 'hand-holding', 'mage-eventpress' ),
                'fas fa-hand-holding-heart' => __( 'hand-holding-heart', 'mage-eventpress' ),
                'fas fa-hand-holding-usd' => __( 'hand-holding-usd', 'mage-eventpress' ),
                'fas fa-hand-lizard' => __( 'hand-lizard', 'mage-eventpress' ),
                'far fa-hand-lizard' => __( 'hand-lizard', 'mage-eventpress' ),
                'fas fa-hand-paper' => __( 'hand-paper', 'mage-eventpress' ),
                'far fa-hand-paper' => __( 'hand-paper', 'mage-eventpress' ),
                'fas fa-hand-peace' => __( 'hand-peace', 'mage-eventpress' ),
                'far fa-hand-peace' => __( 'hand-peace', 'mage-eventpress' ),
                'fas fa-hand-point-down' => __( 'hand-point-down', 'mage-eventpress' ),
                'far fa-hand-point-down' => __( 'hand-point-down', 'mage-eventpress' ),
                'fas fa-hand-point-left' => __( 'hand-point-left', 'mage-eventpress' ),
                'far fa-hand-point-left' => __( 'hand-point-left', 'mage-eventpress' ),
                'fas fa-hand-point-right' => __( 'hand-point-right', 'mage-eventpress' ),
                'far fa-hand-point-right' => __( 'hand-point-right', 'mage-eventpress' ),
                'fas fa-hand-point-up' => __( 'hand-point-up', 'mage-eventpress' ),
                'far fa-hand-point-up' => __( 'hand-point-up', 'mage-eventpress' ),
                'fas fa-hand-pointer' => __( 'hand-pointer', 'mage-eventpress' ),
                'far fa-hand-pointer' => __( 'hand-pointer', 'mage-eventpress' ),
                'fas fa-hand-rock' => __( 'hand-rock', 'mage-eventpress' ),
                'far fa-hand-rock' => __( 'hand-rock', 'mage-eventpress' ),
                'fas fa-hand-scissors' => __( 'hand-scissors', 'mage-eventpress' ),
                'far fa-hand-scissors' => __( 'hand-scissors', 'mage-eventpress' ),
                'fas fa-hand-spock' => __( 'hand-spock', 'mage-eventpress' ),
                'far fa-hand-spock' => __( 'hand-spock', 'mage-eventpress' ),
                'fas fa-hands' => __( 'hands', 'mage-eventpress' ),
                'fas fa-hands-helping' => __( 'hands-helping', 'mage-eventpress' ),
                'fas fa-handshake' => __( 'handshake', 'mage-eventpress' ),
                'far fa-handshake' => __( 'handshake', 'mage-eventpress' ),
                'fas fa-hashtag' => __( 'hashtag', 'mage-eventpress' ),
                'fas fa-hdd' => __( 'hdd', 'mage-eventpress' ),
                'far fa-hdd' => __( 'hdd', 'mage-eventpress' ),
                'fas fa-heading' => __( 'heading', 'mage-eventpress' ),
                'fas fa-headphones' => __( 'headphones', 'mage-eventpress' ),
                'fas fa-heart' => __( 'heart', 'mage-eventpress' ),
                'far fa-heart' => __( 'heart', 'mage-eventpress' ),
                'fas fa-heartbeat' => __( 'heartbeat', 'mage-eventpress' ),
                'fab fa-hips' => __( 'hips', 'mage-eventpress' ),
                'fab fa-hire-a-helper' => __( 'hire-a-helper', 'mage-eventpress' ),
                'fas fa-history' => __( 'history', 'mage-eventpress' ),
                'fas fa-hockey-puck' => __( 'hockey-puck', 'mage-eventpress' ),
                'fas fa-home' => __( 'home', 'mage-eventpress' ),
                'fab fa-hooli' => __( 'hooli', 'mage-eventpress' ),
                'fas fa-hospital' => __( 'hospital', 'mage-eventpress' ),
                'far fa-hospital' => __( 'hospital', 'mage-eventpress' ),
                'fas fa-hospital-alt' => __( 'hospital-alt', 'mage-eventpress' ),
                'fas fa-hospital-symbol' => __( 'hospital-symbol', 'mage-eventpress' ),
                'fab fa-hotjar' => __( 'hotjar', 'mage-eventpress' ),
                'fas fa-hourglass' => __( 'hourglass', 'mage-eventpress' ),
                'far fa-hourglass' => __( 'hourglass', 'mage-eventpress' ),
                'fas fa-hourglass-end' => __( 'hourglass-end', 'mage-eventpress' ),
                'fas fa-hourglass-half' => __( 'hourglass-half', 'mage-eventpress' ),
                'fas fa-hourglass-start' => __( 'hourglass-start', 'mage-eventpress' ),
                'fab fa-houzz' => __( 'houzz', 'mage-eventpress' ),
                'fab fa-html5' => __( 'html5', 'mage-eventpress' ),
                'fab fa-hubspot' => __( 'hubspot', 'mage-eventpress' ),
                'fas fa-i-cursor' => __( 'i-cursor', 'mage-eventpress' ),
                'fas fa-id-badge' => __( 'id-badge', 'mage-eventpress' ),
                'far fa-id-badge' => __( 'id-badge', 'mage-eventpress' ),
                'fas fa-id-card' => __( 'id-card', 'mage-eventpress' ),
                'far fa-id-card' => __( 'id-card', 'mage-eventpress' ),
                'fas fa-id-card-alt' => __( 'id-card-alt', 'mage-eventpress' ),
                'fas fa-image' => __( 'image', 'mage-eventpress' ),
                'far fa-image' => __( 'image', 'mage-eventpress' ),
                'fas fa-images' => __( 'images', 'mage-eventpress' ),
                'far fa-images' => __( 'images', 'mage-eventpress' ),
                'fab fa-imdb' => __( 'imdb', 'mage-eventpress' ),
                'fas fa-inbox' => __( 'inbox', 'mage-eventpress' ),
                'fas fa-indent' => __( 'indent', 'mage-eventpress' ),
                'fas fa-industry' => __( 'industry', 'mage-eventpress' ),
                'fas fa-info' => __( 'info', 'mage-eventpress' ),
                'fas fa-info-circle' => __( 'info-circle', 'mage-eventpress' ),
                'fab fa-instagram' => __( 'instagram', 'mage-eventpress' ),
                'fab fa-internet-explorer' => __( 'internet-explorer', 'mage-eventpress' ),
                'fab fa-ioxhost' => __( 'ioxhost', 'mage-eventpress' ),
                'fas fa-italic' => __( 'italic', 'mage-eventpress' ),
                'fab fa-itunes' => __( 'itunes', 'mage-eventpress' ),
                'fab fa-itunes-note' => __( 'itunes-note', 'mage-eventpress' ),
                'fab fa-java' => __( 'java', 'mage-eventpress' ),
                'fab fa-jenkins' => __( 'jenkins', 'mage-eventpress' ),
                'fab fa-joget' => __( 'joget', 'mage-eventpress' ),
                'fab fa-joomla' => __( 'joomla', 'mage-eventpress' ),
                'fab fa-js' => __( 'js', 'mage-eventpress' ),
                'fab fa-js-square' => __( 'js-square', 'mage-eventpress' ),
                'fab fa-jsfiddle' => __( 'jsfiddle', 'mage-eventpress' ),
                'fas fa-key' => __( 'key', 'mage-eventpress' ),
                'fas fa-keyboard' => __( 'keyboard', 'mage-eventpress' ),
                'far fa-keyboard' => __( 'keyboard', 'mage-eventpress' ),
                'fab fa-keycdn' => __( 'keycdn', 'mage-eventpress' ),
                'fab fa-kickstarter' => __( 'kickstarter', 'mage-eventpress' ),
                'fab fa-kickstarter-k' => __( 'kickstarter-k', 'mage-eventpress' ),
                'fab fa-korvue' => __( 'korvue', 'mage-eventpress' ),
                'fas fa-language' => __( 'language', 'mage-eventpress' ),
                'fas fa-laptop' => __( 'laptop', 'mage-eventpress' ),
                'fab fa-laravel' => __( 'laravel', 'mage-eventpress' ),
                'fab fa-lastfm' => __( 'lastfm', 'mage-eventpress' ),
                'fab fa-lastfm-square' => __( 'lastfm-square', 'mage-eventpress' ),
                'fas fa-leaf' => __( 'leaf', 'mage-eventpress' ),
                'fab fa-leanpub' => __( 'leanpub', 'mage-eventpress' ),
                'fas fa-lemon' => __( 'lemon', 'mage-eventpress' ),
                'far fa-lemon' => __( 'lemon', 'mage-eventpress' ),
                'fab fa-less' => __( 'less', 'mage-eventpress' ),
                'fas fa-level-down-alt' => __( 'level-down-alt', 'mage-eventpress' ),
                'fas fa-level-up-alt' => __( 'level-up-alt', 'mage-eventpress' ),
                'fas fa-life-ring' => __( 'life-ring', 'mage-eventpress' ),
                'far fa-life-ring' => __( 'life-ring', 'mage-eventpress' ),
                'fas fa-lightbulb' => __( 'lightbulb', 'mage-eventpress' ),
                'far fa-lightbulb' => __( 'lightbulb', 'mage-eventpress' ),
                'fab fa-line' => __( 'line', 'mage-eventpress' ),
                'fas fa-link' => __( 'link', 'mage-eventpress' ),
                'fab fa-linkedin' => __( 'linkedin', 'mage-eventpress' ),
                'fab fa-linkedin-in' => __( 'linkedin-in', 'mage-eventpress' ),
                'fab fa-linode' => __( 'linode', 'mage-eventpress' ),
                'fab fa-linux' => __( 'linux', 'mage-eventpress' ),
                'fas fa-lira-sign' => __( 'lira-sign', 'mage-eventpress' ),
                'fas fa-list' => __( 'list', 'mage-eventpress' ),
                'fas fa-list-alt' => __( 'list-alt', 'mage-eventpress' ),
                'far fa-list-alt' => __( 'list-alt', 'mage-eventpress' ),
                'fas fa-list-ol' => __( 'list-ol', 'mage-eventpress' ),
                'fas fa-list-ul' => __( 'list-ul', 'mage-eventpress' ),
                'fas fa-location-arrow' => __( 'location-arrow', 'mage-eventpress' ),
                'fas fa-lock' => __( 'lock', 'mage-eventpress' ),
                'fas fa-lock-open' => __( 'lock-open', 'mage-eventpress' ),
                'fas fa-long-arrow-alt-down' => __( 'long-arrow-alt-down', 'mage-eventpress' ),
                'fas fa-long-arrow-alt-left' => __( 'long-arrow-alt-left', 'mage-eventpress' ),
                'fas fa-long-arrow-alt-right' => __( 'long-arrow-alt-right', 'mage-eventpress' ),
                'fas fa-long-arrow-alt-up' => __( 'long-arrow-alt-up', 'mage-eventpress' ),
                'fas fa-low-vision' => __( 'low-vision', 'mage-eventpress' ),
                'fab fa-lyft' => __( 'lyft', 'mage-eventpress' ),
                'fab fa-magento' => __( 'magento', 'mage-eventpress' ),
                'fas fa-magic' => __( 'magic', 'mage-eventpress' ),
                'fas fa-magnet' => __( 'magnet', 'mage-eventpress' ),
                'fas fa-male' => __( 'male', 'mage-eventpress' ),
                'fas fa-map' => __( 'map', 'mage-eventpress' ),
                'far fa-map' => __( 'map', 'mage-eventpress' ),
                'fas fa-map-marker' => __( 'map-marker', 'mage-eventpress' ),
                'fas fa-map-marker-alt' => __( 'map-marker-alt', 'mage-eventpress' ),
                'fas fa-map-pin' => __( 'map-pin', 'mage-eventpress' ),
                'fas fa-map-signs' => __( 'map-signs', 'mage-eventpress' ),
                'fas fa-mars' => __( 'mars', 'mage-eventpress' ),
                'fas fa-mars-double' => __( 'mars-double', 'mage-eventpress' ),
                'fas fa-mars-stroke' => __( 'mars-stroke', 'mage-eventpress' ),
                'fas fa-mars-stroke-h' => __( 'mars-stroke-h', 'mage-eventpress' ),
                'fas fa-mars-stroke-v' => __( 'mars-stroke-v', 'mage-eventpress' ),
                'fab fa-maxcdn' => __( 'maxcdn', 'mage-eventpress' ),
                'fab fa-medapps' => __( 'medapps', 'mage-eventpress' ),
                'fab fa-medium' => __( 'medium', 'mage-eventpress' ),
                'fab fa-medium-m' => __( 'medium-m', 'mage-eventpress' ),
                'fas fa-medkit' => __( 'medkit', 'mage-eventpress' ),
                'fab fa-medrt' => __( 'medrt', 'mage-eventpress' ),
                'fab fa-meetup' => __( 'meetup', 'mage-eventpress' ),
                'fas fa-meh' => __( 'meh', 'mage-eventpress' ),
                'far fa-meh' => __( 'meh', 'mage-eventpress' ),
                'fas fa-mercury' => __( 'mercury', 'mage-eventpress' ),
                'fas fa-microchip' => __( 'microchip', 'mage-eventpress' ),
                'fas fa-microphone' => __( 'microphone', 'mage-eventpress' ),
                'fas fa-microphone-slash' => __( 'microphone-slash', 'mage-eventpress' ),
                'fab fa-microsoft' => __( 'microsoft', 'mage-eventpress' ),
                'fas fa-minus' => __( 'minus', 'mage-eventpress' ),
                'fas fa-minus-circle' => __( 'minus-circle', 'mage-eventpress' ),
                'fas fa-minus-square' => __( 'minus-square', 'mage-eventpress' ),
                'far fa-minus-square' => __( 'minus-square', 'mage-eventpress' ),
                'fab fa-mix' => __( 'mix', 'mage-eventpress' ),
                'fab fa-mixcloud' => __( 'mixcloud', 'mage-eventpress' ),
                'fab fa-mizuni' => __( 'mizuni', 'mage-eventpress' ),
                'fas fa-mobile' => __( 'mobile', 'mage-eventpress' ),
                'fas fa-mobile-alt' => __( 'mobile-alt', 'mage-eventpress' ),
                'fab fa-modx' => __( 'modx', 'mage-eventpress' ),
                'fab fa-monero' => __( 'monero', 'mage-eventpress' ),
                'fas fa-money-bill-alt' => __( 'money-bill-alt', 'mage-eventpress' ),
                'far fa-money-bill-alt' => __( 'money-bill-alt', 'mage-eventpress' ),
                'fas fa-moon' => __( 'moon', 'mage-eventpress' ),
                'far fa-moon' => __( 'moon', 'mage-eventpress' ),
                'fas fa-motorcycle' => __( 'motorcycle', 'mage-eventpress' ),
                'fas fa-mouse-pointer' => __( 'mouse-pointer', 'mage-eventpress' ),
                'fas fa-music' => __( 'music', 'mage-eventpress' ),
                'fab fa-napster' => __( 'napster', 'mage-eventpress' ),
                'fas fa-neuter' => __( 'neuter', 'mage-eventpress' ),
                'fas fa-newspaper' => __( 'newspaper', 'mage-eventpress' ),
                'far fa-newspaper' => __( 'newspaper', 'mage-eventpress' ),
                'fab fa-nintendo-switch' => __( 'nintendo-switch', 'mage-eventpress' ),
                'fab fa-node' => __( 'node', 'mage-eventpress' ),
                'fab fa-node-js' => __( 'node-js', 'mage-eventpress' ),
                'fas fa-notes-medical' => __( 'notes-medical', 'mage-eventpress' ),
                'fab fa-npm' => __( 'npm', 'mage-eventpress' ),
                'fab fa-ns8' => __( 'ns8', 'mage-eventpress' ),
                'fab fa-nutritionix' => __( 'nutritionix', 'mage-eventpress' ),
                'fas fa-object-group' => __( 'object-group', 'mage-eventpress' ),
                'far fa-object-group' => __( 'object-group', 'mage-eventpress' ),
                'fas fa-object-ungroup' => __( 'object-ungroup', 'mage-eventpress' ),
                'far fa-object-ungroup' => __( 'object-ungroup', 'mage-eventpress' ),
                'fab fa-odnoklassniki' => __( 'odnoklassniki', 'mage-eventpress' ),
                'fab fa-odnoklassniki-square' => __( 'odnoklassniki-square', 'mage-eventpress' ),
                'fab fa-opencart' => __( 'opencart', 'mage-eventpress' ),
                'fab fa-openid' => __( 'openid', 'mage-eventpress' ),
                'fab fa-opera' => __( 'opera', 'mage-eventpress' ),
                'fab fa-optin-monster' => __( 'optin-monster', 'mage-eventpress' ),
                'fab fa-osi' => __( 'osi', 'mage-eventpress' ),
                'fas fa-outdent' => __( 'outdent', 'mage-eventpress' ),
                'fab fa-page4' => __( 'page4', 'mage-eventpress' ),
                'fab fa-pagelines' => __( 'pagelines', 'mage-eventpress' ),
                'fas fa-paint-brush' => __( 'paint-brush', 'mage-eventpress' ),
                'fab fa-palfed' => __( 'palfed', 'mage-eventpress' ),
                'fas fa-pallet' => __( 'pallet', 'mage-eventpress' ),
                'fas fa-paper-plane' => __( 'paper-plane', 'mage-eventpress' ),
                'far fa-paper-plane' => __( 'paper-plane', 'mage-eventpress' ),
                'fas fa-paperclip' => __( 'paperclip', 'mage-eventpress' ),
                'fas fa-parachute-box' => __( 'parachute-box', 'mage-eventpress' ),
                'fas fa-paragraph' => __( 'paragraph', 'mage-eventpress' ),
                'fas fa-paste' => __( 'paste', 'mage-eventpress' ),
                'fab fa-patreon' => __( 'patreon', 'mage-eventpress' ),
                'fas fa-pause' => __( 'pause', 'mage-eventpress' ),
                'fas fa-pause-circle' => __( 'pause-circle', 'mage-eventpress' ),
                'far fa-pause-circle' => __( 'pause-circle', 'mage-eventpress' ),
                'fas fa-paw' => __( 'paw', 'mage-eventpress' ),
                'fab fa-paypal' => __( 'paypal', 'mage-eventpress' ),
                'fas fa-pen-square' => __( 'pen-square', 'mage-eventpress' ),
                'fas fa-pencil-alt' => __( 'pencil-alt', 'mage-eventpress' ),
                'fas fa-people-carry' => __( 'people-carry', 'mage-eventpress' ),
                'fas fa-percent' => __( 'percent', 'mage-eventpress' ),
                'fab fa-periscope' => __( 'periscope', 'mage-eventpress' ),
                'fab fa-phabricator' => __( 'phabricator', 'mage-eventpress' ),
                'fab fa-phoenix-framework' => __( 'phoenix-framework', 'mage-eventpress' ),
                'fas fa-phone' => __( 'phone', 'mage-eventpress' ),
                'fas fa-phone-slash' => __( 'phone-slash', 'mage-eventpress' ),
                'fas fa-phone-square' => __( 'phone-square', 'mage-eventpress' ),
                'fas fa-phone-volume' => __( 'phone-volume', 'mage-eventpress' ),
                'fab fa-php' => __( 'php', 'mage-eventpress' ),
                'fab fa-pied-piper' => __( 'pied-piper', 'mage-eventpress' ),
                'fab fa-pied-piper-alt' => __( 'pied-piper-alt', 'mage-eventpress' ),
                'fab fa-pied-piper-hat' => __( 'pied-piper-hat', 'mage-eventpress' ),
                'fab fa-pied-piper-pp' => __( 'pied-piper-pp', 'mage-eventpress' ),
                'fas fa-piggy-bank' => __( 'piggy-bank', 'mage-eventpress' ),
                'fas fa-pills' => __( 'pills', 'mage-eventpress' ),
                'fab fa-pinterest' => __( 'pinterest', 'mage-eventpress' ),
                'fab fa-pinterest-p' => __( 'pinterest-p', 'mage-eventpress' ),
                'fab fa-pinterest-square' => __( 'pinterest-square', 'mage-eventpress' ),
                'fas fa-plane' => __( 'plane', 'mage-eventpress' ),
                'fas fa-play' => __( 'play', 'mage-eventpress' ),
                'fas fa-play-circle' => __( 'play-circle', 'mage-eventpress' ),
                'far fa-play-circle' => __( 'play-circle', 'mage-eventpress' ),
                'fab fa-playstation' => __( 'playstation', 'mage-eventpress' ),
                'fas fa-plug' => __( 'plug', 'mage-eventpress' ),
                'fas fa-plus' => __( 'plus', 'mage-eventpress' ),
                'fas fa-plus-circle' => __( 'plus-circle', 'mage-eventpress' ),
                'fas fa-plus-square' => __( 'plus-square', 'mage-eventpress' ),
                'far fa-plus-square' => __( 'plus-square', 'mage-eventpress' ),
                'fas fa-podcast' => __( 'podcast', 'mage-eventpress' ),
                'fas fa-poo' => __( 'poo', 'mage-eventpress' ),
                'fas fa-pound-sign' => __( 'pound-sign', 'mage-eventpress' ),
                'fas fa-power-off' => __( 'power-off', 'mage-eventpress' ),
                'fas fa-prescription-bottle' => __( 'prescription-bottle', 'mage-eventpress' ),
                'fas fa-prescription-bottle-alt' => __( 'prescription-bottle-alt', 'mage-eventpress' ),
                'fas fa-print' => __( 'print', 'mage-eventpress' ),
                'fas fa-procedures' => __( 'procedures', 'mage-eventpress' ),
                'fab fa-product-hunt' => __( 'product-hunt', 'mage-eventpress' ),
                'fab fa-pushed' => __( 'pushed', 'mage-eventpress' ),
                'fas fa-puzzle-piece' => __( 'puzzle-piece', 'mage-eventpress' ),
                'fab fa-python' => __( 'python', 'mage-eventpress' ),
                'fab fa-qq' => __( 'qq', 'mage-eventpress' ),
                'fas fa-qrcode' => __( 'qrcode', 'mage-eventpress' ),
                'fas fa-question' => __( 'question', 'mage-eventpress' ),
                'fas fa-question-circle' => __( 'question-circle', 'mage-eventpress' ),
                'far fa-question-circle' => __( 'question-circle', 'mage-eventpress' ),
                'fas fa-quidditch' => __( 'quidditch', 'mage-eventpress' ),
                'fab fa-quinscape' => __( 'quinscape', 'mage-eventpress' ),
                'fab fa-quora' => __( 'quora', 'mage-eventpress' ),
                'fas fa-quote-left' => __( 'quote-left', 'mage-eventpress' ),
                'fas fa-quote-right' => __( 'quote-right', 'mage-eventpress' ),
                'fas fa-random' => __( 'random', 'mage-eventpress' ),
                'fab fa-ravelry' => __( 'ravelry', 'mage-eventpress' ),
                'fab fa-react' => __( 'react', 'mage-eventpress' ),
                'fab fa-readme' => __( 'readme', 'mage-eventpress' ),
                'fab fa-rebel' => __( 'rebel', 'mage-eventpress' ),
                'fas fa-recycle' => __( 'recycle', 'mage-eventpress' ),
                'fab fa-red-river' => __( 'red-river', 'mage-eventpress' ),
                'fab fa-reddit' => __( 'reddit', 'mage-eventpress' ),
                'fab fa-reddit-alien' => __( 'reddit-alien', 'mage-eventpress' ),
                'fab fa-reddit-square' => __( 'reddit-square', 'mage-eventpress' ),
                'fas fa-redo' => __( 'redo', 'mage-eventpress' ),
                'fas fa-redo-alt' => __( 'redo-alt', 'mage-eventpress' ),
                'fas fa-registered' => __( 'registered', 'mage-eventpress' ),
                'far fa-registered' => __( 'registered', 'mage-eventpress' ),
                'fab fa-rendact' => __( 'rendact', 'mage-eventpress' ),
                'fab fa-renren' => __( 'renren', 'mage-eventpress' ),
                'fas fa-reply' => __( 'reply', 'mage-eventpress' ),
                'fas fa-reply-all' => __( 'reply-all', 'mage-eventpress' ),
                'fab fa-replyd' => __( 'replyd', 'mage-eventpress' ),
                'fab fa-resolving' => __( 'resolving', 'mage-eventpress' ),
                'fas fa-retweet' => __( 'retweet', 'mage-eventpress' ),
                'fas fa-ribbon' => __( 'ribbon', 'mage-eventpress' ),
                'fas fa-road' => __( 'road', 'mage-eventpress' ),
                'fas fa-rocket' => __( 'rocket', 'mage-eventpress' ),
                'fab fa-rocketchat' => __( 'rocketchat', 'mage-eventpress' ),
                'fab fa-rockrms' => __( 'rockrms', 'mage-eventpress' ),
                'fas fa-rss' => __( 'rss', 'mage-eventpress' ),
                'fas fa-rss-square' => __( 'rss-square', 'mage-eventpress' ),
                'fas fa-ruble-sign' => __( 'ruble-sign', 'mage-eventpress' ),
                'fas fa-rupee-sign' => __( 'rupee-sign', 'mage-eventpress' ),
                'fab fa-safari' => __( 'safari', 'mage-eventpress' ),
                'fab fa-sass' => __( 'sass', 'mage-eventpress' ),
                'fas fa-save' => __( 'save', 'mage-eventpress' ),
                'far fa-save' => __( 'save', 'mage-eventpress' ),
                'fab fa-schlix' => __( 'schlix', 'mage-eventpress' ),
                'fab fa-scribd' => __( 'scribd', 'mage-eventpress' ),
                'fas fa-search' => __( 'search', 'mage-eventpress' ),
                'fas fa-search-minus' => __( 'search-minus', 'mage-eventpress' ),
                'fas fa-search-plus' => __( 'search-plus', 'mage-eventpress' ),
                'fab fa-searchengin' => __( 'searchengin', 'mage-eventpress' ),
                'fas fa-seedling' => __( 'seedling', 'mage-eventpress' ),
                'fab fa-sellcast' => __( 'sellcast', 'mage-eventpress' ),
                'fab fa-sellsy' => __( 'sellsy', 'mage-eventpress' ),
                'fas fa-server' => __( 'server', 'mage-eventpress' ),
                'fab fa-servicestack' => __( 'servicestack', 'mage-eventpress' ),
                'fas fa-share' => __( 'share', 'mage-eventpress' ),
                'fas fa-share-alt' => __( 'share-alt', 'mage-eventpress' ),
                'fas fa-share-alt-square' => __( 'share-alt-square', 'mage-eventpress' ),
                'fas fa-share-square' => __( 'share-square', 'mage-eventpress' ),
                'far fa-share-square' => __( 'share-square', 'mage-eventpress' ),
                'fas fa-shekel-sign' => __( 'shekel-sign', 'mage-eventpress' ),
                'fas fa-shield-alt' => __( 'shield-alt', 'mage-eventpress' ),
                'fas fa-ship' => __( 'ship', 'mage-eventpress' ),
                'fas fa-shipping-fast' => __( 'shipping-fast', 'mage-eventpress' ),
                'fab fa-shirtsinbulk' => __( 'shirtsinbulk', 'mage-eventpress' ),
                'fas fa-shopping-bag' => __( 'shopping-bag', 'mage-eventpress' ),
                'fas fa-shopping-basket' => __( 'shopping-basket', 'mage-eventpress' ),
                'fas fa-shopping-cart' => __( 'shopping-cart', 'mage-eventpress' ),
                'fas fa-shower' => __( 'shower', 'mage-eventpress' ),
                'fas fa-sign' => __( 'sign', 'mage-eventpress' ),
                'fas fa-sign-in-alt' => __( 'sign-in-alt', 'mage-eventpress' ),
                'fas fa-sign-language' => __( 'sign-language', 'mage-eventpress' ),
                'fas fa-sign-out-alt' => __( 'sign-out-alt', 'mage-eventpress' ),
                'fas fa-signal' => __( 'signal', 'mage-eventpress' ),
                'fab fa-simplybuilt' => __( 'simplybuilt', 'mage-eventpress' ),
                'fab fa-sistrix' => __( 'sistrix', 'mage-eventpress' ),
                'fas fa-sitemap' => __( 'sitemap', 'mage-eventpress' ),
                'fab fa-skyatlas' => __( 'skyatlas', 'mage-eventpress' ),
                'fab fa-skype' => __( 'skype', 'mage-eventpress' ),
                'fab fa-slack' => __( 'slack', 'mage-eventpress' ),
                'fab fa-slack-hash' => __( 'slack-hash', 'mage-eventpress' ),
                'fas fa-sliders-h' => __( 'sliders-h', 'mage-eventpress' ),
                'fab fa-slideshare' => __( 'slideshare', 'mage-eventpress' ),
                'fas fa-smile' => __( 'smile', 'mage-eventpress' ),
                'far fa-smile' => __( 'smile', 'mage-eventpress' ),
                'fas fa-smoking' => __( 'smoking', 'mage-eventpress' ),
                'fab fa-snapchat' => __( 'snapchat', 'mage-eventpress' ),
                'fab fa-snapchat-ghost' => __( 'snapchat-ghost', 'mage-eventpress' ),
                'fab fa-snapchat-square' => __( 'snapchat-square', 'mage-eventpress' ),
                'fas fa-snowflake' => __( 'snowflake', 'mage-eventpress' ),
                'far fa-snowflake' => __( 'snowflake', 'mage-eventpress' ),
                'fas fa-sort' => __( 'sort', 'mage-eventpress' ),
                'fas fa-sort-alpha-down' => __( 'sort-alpha-down', 'mage-eventpress' ),
                'fas fa-sort-alpha-up' => __( 'sort-alpha-up', 'mage-eventpress' ),
                'fas fa-sort-amount-down' => __( 'sort-amount-down', 'mage-eventpress' ),
                'fas fa-sort-amount-up' => __( 'sort-amount-up', 'mage-eventpress' ),
                'fas fa-sort-down' => __( 'sort-down', 'mage-eventpress' ),
                'fas fa-sort-numeric-down' => __( 'sort-numeric-down', 'mage-eventpress' ),
                'fas fa-sort-numeric-up' => __( 'sort-numeric-up', 'mage-eventpress' ),
                'fas fa-sort-up' => __( 'sort-up', 'mage-eventpress' ),
                'fab fa-soundcloud' => __( 'soundcloud', 'mage-eventpress' ),
                'fas fa-space-shuttle' => __( 'space-shuttle', 'mage-eventpress' ),
                'fab fa-speakap' => __( 'speakap', 'mage-eventpress' ),
                'fas fa-spinner' => __( 'spinner', 'mage-eventpress' ),
                'fab fa-spotify' => __( 'spotify', 'mage-eventpress' ),
                'fas fa-square' => __( 'square', 'mage-eventpress' ),
                'far fa-square' => __( 'square', 'mage-eventpress' ),
                'fas fa-square-full' => __( 'square-full', 'mage-eventpress' ),
                'fab fa-stack-exchange' => __( 'stack-exchange', 'mage-eventpress' ),
                'fab fa-stack-overflow' => __( 'stack-overflow', 'mage-eventpress' ),
                'fas fa-star' => __( 'star', 'mage-eventpress' ),
                'far fa-star' => __( 'star', 'mage-eventpress' ),
                'fas fa-star-half' => __( 'star-half', 'mage-eventpress' ),
                'far fa-star-half' => __( 'star-half', 'mage-eventpress' ),
                'fab fa-staylinked' => __( 'staylinked', 'mage-eventpress' ),
                'fab fa-steam' => __( 'steam', 'mage-eventpress' ),
                'fab fa-steam-square' => __( 'steam-square', 'mage-eventpress' ),
                'fab fa-steam-symbol' => __( 'steam-symbol', 'mage-eventpress' ),
                'fas fa-step-backward' => __( 'step-backward', 'mage-eventpress' ),
                'fas fa-step-forward' => __( 'step-forward', 'mage-eventpress' ),
                'fas fa-stethoscope' => __( 'stethoscope', 'mage-eventpress' ),
                'fab fa-sticker-mule' => __( 'sticker-mule', 'mage-eventpress' ),
                'fas fa-sticky-note' => __( 'sticky-note', 'mage-eventpress' ),
                'far fa-sticky-note' => __( 'sticky-note', 'mage-eventpress' ),
                'fas fa-stop' => __( 'stop', 'mage-eventpress' ),
                'fas fa-stop-circle' => __( 'stop-circle', 'mage-eventpress' ),
                'far fa-stop-circle' => __( 'stop-circle', 'mage-eventpress' ),
                'fas fa-stopwatch' => __( 'stopwatch', 'mage-eventpress' ),
                'fab fa-strava' => __( 'strava', 'mage-eventpress' ),
                'fas fa-street-view' => __( 'street-view', 'mage-eventpress' ),
                'fas fa-strikethrough' => __( 'strikethrough', 'mage-eventpress' ),
                'fab fa-stripe' => __( 'stripe', 'mage-eventpress' ),
                'fab fa-stripe-s' => __( 'stripe-s', 'mage-eventpress' ),
                'fab fa-studiovinari' => __( 'studiovinari', 'mage-eventpress' ),
                'fab fa-stumbleupon' => __( 'stumbleupon', 'mage-eventpress' ),
                'fab fa-stumbleupon-circle' => __( 'stumbleupon-circle', 'mage-eventpress' ),
                'fas fa-subscript' => __( 'subscript', 'mage-eventpress' ),
                'fas fa-subway' => __( 'subway', 'mage-eventpress' ),
                'fas fa-suitcase' => __( 'suitcase', 'mage-eventpress' ),
                'fas fa-sun' => __( 'sun', 'mage-eventpress' ),
                'far fa-sun' => __( 'sun', 'mage-eventpress' ),
                'fab fa-superpowers' => __( 'superpowers', 'mage-eventpress' ),
                'fas fa-superscript' => __( 'superscript', 'mage-eventpress' ),
                'fab fa-supple' => __( 'supple', 'mage-eventpress' ),
                'fas fa-sync' => __( 'sync', 'mage-eventpress' ),
                'fas fa-sync-alt' => __( 'sync-alt', 'mage-eventpress' ),
                'fas fa-syringe' => __( 'syringe', 'mage-eventpress' ),
                'fas fa-table' => __( 'table', 'mage-eventpress' ),
                'fas fa-table-tennis' => __( 'table-tennis', 'mage-eventpress' ),
                'fas fa-tablet' => __( 'tablet', 'mage-eventpress' ),
                'fas fa-tablet-alt' => __( 'tablet-alt', 'mage-eventpress' ),
                'fas fa-tablets' => __( 'tablets', 'mage-eventpress' ),
                'fas fa-tachometer-alt' => __( 'tachometer-alt', 'mage-eventpress' ),
                'fas fa-tag' => __( 'tag', 'mage-eventpress' ),
                'fas fa-tags' => __( 'tags', 'mage-eventpress' ),
                'fas fa-tape' => __( 'tape', 'mage-eventpress' ),
                'fas fa-tasks' => __( 'tasks', 'mage-eventpress' ),
                'fas fa-taxi' => __( 'taxi', 'mage-eventpress' ),
                'fab fa-telegram' => __( 'telegram', 'mage-eventpress' ),
                'fab fa-telegram-plane' => __( 'telegram-plane', 'mage-eventpress' ),
                'fab fa-tencent-weibo' => __( 'tencent-weibo', 'mage-eventpress' ),
                'fas fa-terminal' => __( 'terminal', 'mage-eventpress' ),
                'fas fa-text-height' => __( 'text-height', 'mage-eventpress' ),
                'fas fa-text-width' => __( 'text-width', 'mage-eventpress' ),
                'fas fa-th' => __( 'th', 'mage-eventpress' ),
                'fas fa-th-large' => __( 'th-large', 'mage-eventpress' ),
                'fas fa-th-list' => __( 'th-list', 'mage-eventpress' ),
                'fab fa-themeisle' => __( 'themeisle', 'mage-eventpress' ),
                'fas fa-thermometer' => __( 'thermometer', 'mage-eventpress' ),
                'fas fa-thermometer-empty' => __( 'thermometer-empty', 'mage-eventpress' ),
                'fas fa-thermometer-full' => __( 'thermometer-full', 'mage-eventpress' ),
                'fas fa-thermometer-half' => __( 'thermometer-half', 'mage-eventpress' ),
                'fas fa-thermometer-quarter' => __( 'thermometer-quarter', 'mage-eventpress' ),
                'fas fa-thermometer-three-quarters' => __( 'thermometer-three-quarters', 'mage-eventpress' ),
                'fas fa-thumbs-down' => __( 'thumbs-down', 'mage-eventpress' ),
                'far fa-thumbs-down' => __( 'thumbs-down', 'mage-eventpress' ),
                'fas fa-thumbs-up' => __( 'thumbs-up', 'mage-eventpress' ),
                'far fa-thumbs-up' => __( 'thumbs-up', 'mage-eventpress' ),
                'fas fa-thumbtack' => __( 'thumbtack', 'mage-eventpress' ),
                'fas fa-ticket-alt' => __( 'ticket-alt', 'mage-eventpress' ),
                'fas fa-times' => __( 'times', 'mage-eventpress' ),
                'fas fa-times-circle' => __( 'times-circle', 'mage-eventpress' ),
                'far fa-times-circle' => __( 'times-circle', 'mage-eventpress' ),
                'fas fa-tint' => __( 'tint', 'mage-eventpress' ),
                'fas fa-toggle-off' => __( 'toggle-off', 'mage-eventpress' ),
                'fas fa-toggle-on' => __( 'toggle-on', 'mage-eventpress' ),
                'fas fa-trademark' => __( 'trademark', 'mage-eventpress' ),
                'fas fa-train' => __( 'train', 'mage-eventpress' ),
                'fas fa-transgender' => __( 'transgender', 'mage-eventpress' ),
                'fas fa-transgender-alt' => __( 'transgender-alt', 'mage-eventpress' ),
                'fas fa-trash' => __( 'trash', 'mage-eventpress' ),
                'fas fa-trash-alt' => __( 'trash-alt', 'mage-eventpress' ),
                'far fa-trash-alt' => __( 'trash-alt', 'mage-eventpress' ),
                'fas fa-tree' => __( 'tree', 'mage-eventpress' ),
                'fab fa-trello' => __( 'trello', 'mage-eventpress' ),
                'fab fa-tripadvisor' => __( 'tripadvisor', 'mage-eventpress' ),
                'fas fa-trophy' => __( 'trophy', 'mage-eventpress' ),
                'fas fa-truck' => __( 'truck', 'mage-eventpress' ),
                'fas fa-truck-loading' => __( 'truck-loading', 'mage-eventpress' ),
                'fas fa-truck-moving' => __( 'truck-moving', 'mage-eventpress' ),
                'fas fa-tty' => __( 'tty', 'mage-eventpress' ),
                'fab fa-tumblr' => __( 'tumblr', 'mage-eventpress' ),
                'fab fa-tumblr-square' => __( 'tumblr-square', 'mage-eventpress' ),
                'fas fa-tv' => __( 'tv', 'mage-eventpress' ),
                'fab fa-twitch' => __( 'twitch', 'mage-eventpress' ),
                'fab fa-twitter' => __( 'twitter', 'mage-eventpress' ),
                'fab fa-twitter-square' => __( 'twitter-square', 'mage-eventpress' ),
                'fab fa-typo3' => __( 'typo3', 'mage-eventpress' ),
                'fab fa-uber' => __( 'uber', 'mage-eventpress' ),
                'fab fa-uikit' => __( 'uikit', 'mage-eventpress' ),
                'fas fa-umbrella' => __( 'umbrella', 'mage-eventpress' ),
                'fas fa-underline' => __( 'underline', 'mage-eventpress' ),
                'fas fa-undo' => __( 'undo', 'mage-eventpress' ),
                'fas fa-undo-alt' => __( 'undo-alt', 'mage-eventpress' ),
                'fab fa-uniregistry' => __( 'uniregistry', 'mage-eventpress' ),
                'fas fa-universal-access' => __( 'universal-access', 'mage-eventpress' ),
                'fas fa-university' => __( 'university', 'mage-eventpress' ),
                'fas fa-unlink' => __( 'unlink', 'mage-eventpress' ),
                'fas fa-unlock' => __( 'unlock', 'mage-eventpress' ),
                'fas fa-unlock-alt' => __( 'unlock-alt', 'mage-eventpress' ),
                'fab fa-untappd' => __( 'untappd', 'mage-eventpress' ),
                'fas fa-upload' => __( 'upload', 'mage-eventpress' ),
                'fab fa-usb' => __( 'usb', 'mage-eventpress' ),
                'fas fa-user' => __( 'user', 'mage-eventpress' ),
                'far fa-user' => __( 'user', 'mage-eventpress' ),
                'fas fa-user-circle' => __( 'user-circle', 'mage-eventpress' ),
                'far fa-user-circle' => __( 'user-circle', 'mage-eventpress' ),
                'fas fa-user-md' => __( 'user-md', 'mage-eventpress' ),
                'fas fa-user-plus' => __( 'user-plus', 'mage-eventpress' ),
                'fas fa-user-secret' => __( 'user-secret', 'mage-eventpress' ),
                'fas fa-user-times' => __( 'user-times', 'mage-eventpress' ),
                'fas fa-users' => __( 'users', 'mage-eventpress' ),
                'fab fa-ussunnah' => __( 'ussunnah', 'mage-eventpress' ),
                'fas fa-utensil-spoon' => __( 'utensil-spoon', 'mage-eventpress' ),
                'fas fa-utensils' => __( 'utensils', 'mage-eventpress' ),
                'fab fa-vaadin' => __( 'vaadin', 'mage-eventpress' ),
                'fas fa-venus' => __( 'venus', 'mage-eventpress' ),
                'fas fa-venus-double' => __( 'venus-double', 'mage-eventpress' ),
                'fas fa-venus-mars' => __( 'venus-mars', 'mage-eventpress' ),
                'fab fa-viacoin' => __( 'viacoin', 'mage-eventpress' ),
                'fab fa-viadeo' => __( 'viadeo', 'mage-eventpress' ),
                'fab fa-viadeo-square' => __( 'viadeo-square', 'mage-eventpress' ),
                'fas fa-vial' => __( 'vial', 'mage-eventpress' ),
                'fas fa-vials' => __( 'vials', 'mage-eventpress' ),
                'fab fa-viber' => __( 'viber', 'mage-eventpress' ),
                'fas fa-video' => __( 'video', 'mage-eventpress' ),
                'fas fa-video-slash' => __( 'video-slash', 'mage-eventpress' ),
                'fab fa-vimeo' => __( 'vimeo', 'mage-eventpress' ),
                'fab fa-vimeo-square' => __( 'vimeo-square', 'mage-eventpress' ),
                'fab fa-vimeo-v' => __( 'vimeo-v', 'mage-eventpress' ),
                'fab fa-vine' => __( 'vine', 'mage-eventpress' ),
                'fab fa-vk' => __( 'vk', 'mage-eventpress' ),
                'fab fa-vnv' => __( 'vnv', 'mage-eventpress' ),
                'fas fa-volleyball-ball' => __( 'volleyball-ball', 'mage-eventpress' ),
                'fas fa-volume-down' => __( 'volume-down', 'mage-eventpress' ),
                'fas fa-volume-off' => __( 'volume-off', 'mage-eventpress' ),
                'fas fa-volume-up' => __( 'volume-up', 'mage-eventpress' ),
                'fab fa-vuejs' => __( 'vuejs', 'mage-eventpress' ),
                'fas fa-warehouse' => __( 'warehouse', 'mage-eventpress' ),
                'fab fa-weibo' => __( 'weibo', 'mage-eventpress' ),
                'fas fa-weight' => __( 'weight', 'mage-eventpress' ),
                'fab fa-weixin' => __( 'weixin', 'mage-eventpress' ),
                'fab fa-whatsapp' => __( 'whatsapp', 'mage-eventpress' ),
                'fab fa-whatsapp-square' => __( 'whatsapp-square', 'mage-eventpress' ),
                'fas fa-wheelchair' => __( 'wheelchair', 'mage-eventpress' ),
                'fab fa-whmcs' => __( 'whmcs', 'mage-eventpress' ),
                'fas fa-wifi' => __( 'wifi', 'mage-eventpress' ),
                'fab fa-wikipedia-w' => __( 'wikipedia-w', 'mage-eventpress' ),
                'fas fa-window-close' => __( 'window-close', 'mage-eventpress' ),
                'far fa-window-close' => __( 'window-close', 'mage-eventpress' ),
                'fas fa-window-maximize' => __( 'window-maximize', 'mage-eventpress' ),
                'far fa-window-maximize' => __( 'window-maximize', 'mage-eventpress' ),
                'fas fa-window-minimize' => __( 'window-minimize', 'mage-eventpress' ),
                'far fa-window-minimize' => __( 'window-minimize', 'mage-eventpress' ),
                'fas fa-window-restore' => __( 'window-restore', 'mage-eventpress' ),
                'far fa-window-restore' => __( 'window-restore', 'mage-eventpress' ),
                'fab fa-windows' => __( 'windows', 'mage-eventpress' ),
                'fas fa-wine-glass' => __( 'wine-glass', 'mage-eventpress' ),
                'fas fa-won-sign' => __( 'won-sign', 'mage-eventpress' ),
                'fab fa-wordpress' => __( 'wordpress', 'mage-eventpress' ),
                'fab fa-wordpress-simple' => __( 'wordpress-simple', 'mage-eventpress' ),
                'fab fa-wpbeginner' => __( 'wpbeginner', 'mage-eventpress' ),
                'fab fa-wpexplorer' => __( 'wpexplorer', 'mage-eventpress' ),
                'fab fa-wpforms' => __( 'wpforms', 'mage-eventpress' ),
                'fas fa-wrench' => __( 'wrench', 'mage-eventpress' ),
                'fas fa-x-ray' => __( 'x-ray', 'mage-eventpress' ),
                'fab fa-xbox' => __( 'xbox', 'mage-eventpress' ),
                'fab fa-xing' => __( 'xing', 'mage-eventpress' ),
                'fab fa-xing-square' => __( 'xing-square', 'mage-eventpress' ),
                'fab fa-y-combinator' => __( 'y-combinator', 'mage-eventpress' ),
                'fab fa-yahoo' => __( 'yahoo', 'mage-eventpress' ),
                'fab fa-yandex' => __( 'yandex', 'mage-eventpress' ),
                'fab fa-yandex-international' => __( 'yandex-international', 'mage-eventpress' ),
                'fab fa-yelp' => __( 'yelp', 'mage-eventpress' ),
                'fas fa-yen-sign' => __( 'yen-sign', 'mage-eventpress' ),
                'fab fa-yoast' => __( 'yoast', 'mage-eventpress' ),
                'fab fa-youtube' => __( 'youtube', 'mage-eventpress' ),
                'fab fa-youtube-square' => __( 'youtube-square', 'mage-eventpress' ),
            );
            return apply_filters( 'FONTAWESOME_ARRAY', $fonts_arr );
        }
    }
    global $wbtmcore;
    $wbtmcore = new FormFieldsGenerator();
}