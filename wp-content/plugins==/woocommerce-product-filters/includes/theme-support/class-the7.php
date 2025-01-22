<?php

namespace WooCommerce_Product_Filter_Plugin\Theme_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class The7 extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wp_enqueue_scripts', 'register_assets' );

		$hook_manager->add_filter( 'wcpf_selectors', 'theme_selectors' );
	}

	public function theme_selectors( $selectors ) {
		$selectors['productsContainer'] = '.dt-products';

		$selectors['pageTitle'] = '.page-title-head';

		$selectors['breadcrumb'] = '.page-title-breadcrumbs';

		return $selectors;
	}

	public function register_assets() {
		wp_add_inline_style(
			'wcpf-plugin-style',
			'
                .wcpf-filter select {
                    height: auto;
                    color: initial;
                    -webkit-appearance: menulist !important;
                    -moz-appearance: menulist !important;
                    appearance: menulist !important;
                
                }
            '
		);

		wp_add_inline_script(
			'wcpf-plugin-script',
			'
                (function () {
                    window.addEventListener("load", function () {
                        var fixWooIsotope = function () {
                            if (dtGlobals.isPhone) {
                                jQuery(window).trigger("scroll");
                                return;
                            }
                        
                            var $container = jQuery(".iso-container");
                            var $dataAttrContainer = $container,
                            i = 0,
                            contWidth = parseInt($dataAttrContainer.attr("data-width")),
                            contNum = parseInt($dataAttrContainer.attr("data-columns")),
                            desktopNum = parseInt($dataAttrContainer.attr("data-desktop-columns-num")),
                            tabletHNum = parseInt($dataAttrContainer.attr("data-h-tablet-columns-num")),
                            tabletVNum = parseInt($dataAttrContainer.attr("data-v-tablet-columns-num")),
                            phoneNum = parseInt($dataAttrContainer.attr("data-phone-columns-num")),
                            contPadding = parseInt($dataAttrContainer.attr("data-padding"));
                        
                            $container.addClass("cont-id-"+i).attr("data-cont-id", i);
                         
                            jQuery(window).off("columnsReady");
                        
                            $container.off("columnsReady.fixWooIsotope").one("columnsReady.fixWooIsotope.IsoInit", function() {
                                $container.addClass("dt-isotope").IsoInitialisation(".iso-item", "masonry", 400);
                                $container.isotope("on", "layoutComplete", function () {
                                    $container.trigger("IsoReady");
                                });
                            });
                            
                            $container.on("columnsReady.fixWooIsotope.IsoLayout", function() {
                                $container.isotope("layout");
                            });
                            
                            $container.one("columnsReady.fixWooIsotope", function() {
                                    jQuery(".preload-me", $container).heightHack();
                            });
                        
                            $container.one("IsoReady", function() {
                                $container.IsoLayzrInitialisation();
                            });
                            
                            jQuery(window).off("debouncedresize.fixWooIsotope").on("debouncedresize.fixWooIsotope", function () {
                                $container.calculateColumns(contWidth, contNum, contPadding, desktopNum, tabletHNum, tabletVNum, phoneNum, "px");
                                if(contPadding > 10){
                                    $container.addClass("mobile-paddings");
                                }
                            }).trigger("debouncedresize.fixWooIsotope");
                            jQuery(window).trigger("scroll");
                        };
                        
                        var fixWooOrdering = function () {
                            jQuery(".woocommerce-ordering-div select").each(function(){
                                jQuery(this).customSelect();
                            });
                        };

                        jQuery(window).on("wcpf_update_products", function () {
                            fixWooIsotope();
                            fixWooOrdering();
                            
                        });
                    });
                })();
            ',
			'after'
		);
	}
}
