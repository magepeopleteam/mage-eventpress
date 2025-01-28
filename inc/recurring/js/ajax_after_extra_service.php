<script>
jQuery(document).ready(function($) {
                $('#mep_recurring_extra_service_list .qty_dec').on('click', function() {
                    let target = $(this).siblings('input');
                    let value = parseInt(target.val()) - 1;
                    qtyPlace(target, value);
                });
                $('#mep_recurring_extra_service_list .qty_inc').on('click', function() {
                    let target = $(this).siblings('input');
                    let value = parseInt(target.val()) + 1;
                    qtyPlace(target, value);
                });
                $('#mep_recurring_extra_service_list .mage_input_group input').on('keyup', function() {
                    let target = $(this);
                    let value = parseInt(target.val());
                    if (target.val().length > 0) {
                        qtyPlace(target, value);
                    }

                });
                $('#mep_recurring_extra_service_list #mage_event_submit').on('submit', function() {
                    if (mageErrorQty()) {
                        return true;
                    }
                    return false;
                });
                $("#mep_recurring_extra_service_list select[name='option_qty[]']").on('blur', function() {
                    mageErrorQty();
                });

                function qtyPlace(target, value) {
                    let minSeat = parseInt(target.attr('min'));
                    let maxSeat = parseInt(target.attr('max'));
                    if (value < minSeat || isNaN(value)) {
                        value = minSeat;
                    }
                    if (value > maxSeat) {
                        value = maxSeat
                    }
                    target.val(value).change();
                    mageErrorQty();

                }

                function mageErrorQty() {
                    let total_ticket = 0;
                    let target = $("[name='option_qty[]']");
                    target.each(function(index) {
                        total_ticket = total_ticket + parseInt($(this).val());
                    });
                    if (total_ticket > 0) {
                        target.removeClass('mage_error');
                        return true;
                    }
                    target.addClass('mage_error');
                    return false;
                }

              
                  jQuery(document).on("change", ".etp", function() {
                      var sum = 0;
                      jQuery(".etp").each(function() {
                          sum += +jQuery(this).val();
                      });
                      jQuery("#ttyttl").html(sum);
                  });
  
                  jQuery("#ttypelist").change(function() {
                      vallllp = jQuery(this).val() + "_";
                      var n = vallllp.split('_');
                      var price = n[0];
                      var ctt = 99;
                      if (vallllp != "_") {
  
                          var currentValue = parseInt(ctt);
                          jQuery('#rowtotal').val(currentValue += parseFloat(price));
                      }
                      if (vallllp == "_") {
                          jQuery('#eventtp').attr('value', 0);
                          jQuery('#eventtp').attr('max', 0);
                          jQuery("#ttypeprice_show").html("")
                      }
                  });
                  function updateTotal() {
                      var total = 0;
                      vallllp = jQuery(this).val() + "_";
                      var n = vallllp.split('_');
                      var price = n[0];
                      total += parseFloat(price);
                      jQuery('#rowtotal').val(total);
                  }
                  //Bind the change event
                  jQuery(".extra-qty-box").on('change', function() {
                      var sum = 0;
                      var total = 0;
                      jQuery('.price_jq').each(function() {
                          var price = jQuery(this);
                          var count = price.closest('tr').find('.extra-qty-box');
                          sum = (parseFloat(price.html().match(/-?(?:\d+(?:\.\d*)?|\.\d+)/)) * count.val());
                          total = total + sum;
                          // price.closest('tr').find('.cart_total_price').html(sum + "â‚´");
  
                      });
					  jQuery('#rowtotal').val(total);
                      jQuery('#usertotal').html(mp_event_wo_commerce_price_format(total));
                      
                  }).change(); //trigger change event on page load
                  <?php
                 $mep_event_ticket_type = get_post_meta($event_id, 'mep_event_ticket_type', true) ? get_post_meta($event_id, 'mep_event_ticket_type', true) : array();
                  if (sizeof($mep_event_ticket_type) > 0 ) {
                      $count = 1;                  
          
                              foreach ($mep_event_ticket_type as $field) {
                                  $ticket_type = $field['option_name_t'];
                              ?>
                                  var inputs = jQuery("#ttyttl").html() || 0;
                                  var inputs = jQuery('#eventpxtp_<?php echo $count; ?>').val() || 0;
                                  var input = parseInt(inputs);
                                  var children = jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0;
  
                                  var selected_ticket = jQuery('#ttyttl').html();
  
                                  if (input < children) {
                                      jQuery('#dadainfo_<?php echo $count; ?>').empty();
                                      children = 0;
                                  }
                                  for (var i = children + 1; i <= input; i++) {
                                      jQuery('#dadainfo_<?php echo $count; ?>').append(
                                          jQuery('<div/>')
                                          .attr("id", "newDiv" + i)
                                          .html("<?php do_action('mep_reg_fields', $estart_date, $event_id, $ticket_type); ?>")
                                      );
                                  }
                                  jQuery('#eventpxtp_<?php echo $count; ?>').on('change', function() {
                                      var inputs = jQuery("#ttyttl").html() || 0;
                                      var inputs = jQuery('#eventpxtp_<?php echo $count; ?>').val() || 0;
                                      var input = parseInt(inputs);
                                      var children = jQuery('#dadainfo_<?php echo $count; ?> > div').length || 0;
                                      jQuery(document).on("change", ".etp", function() {
                                          var TotalQty = 0;
                                          jQuery(".etp").each(function() {
                                              TotalQty += +jQuery(this).val();
                                          });
                                      });
                                      if (input < children) {
                                          jQuery('#dadainfo_<?php echo $count; ?>').empty();
                                          children = 0;
                                      }
                                      for (var i = children + 1; i <= input; i++) {
                                          jQuery('#dadainfo_<?php echo $count; ?>').append(
                                              jQuery('<div/>')
                                              .attr("id", "newDiv" + i)
                                              .html("<?php do_action('mep_reg_fields', $estart_date, $event_id, $ticket_type); ?>")
                                          );
                                      }
                                  });
                                <?php
                                  $count++;
                              }                                                
                  } 
                ?>
  });
  </script>