<?php

/**
 * Generate CSS based on the Customizer settings.
 *
 * @since 1.0
 * @return string
 */
function lqc_learndash_quiz_customizer_inline_css() {

	/* Create variable for serialized array */
	$lqc_option = get_option( 'lqc_quiz_options' );

	// Open :root
	$css = ':root{';

		/**
		 * Primary Color (Dark)
		 */
		if( !empty( $lqc_option['color_primary_dark'] ) ) {
			$css .= '--lqc-color-primary-dark:' . $lqc_option['color_primary_dark'] . ';';
		}

		/**
		 * Primary Color (Light)
		 */
		if( !empty( $lqc_option['color_primary_light'] ) ) {
			$css .= '--lqc-color-primary-light:' . $lqc_option['color_primary_light'] . ';';
		}

		/**
		 * Correct Color (Dark)
		 */
		if( !empty( $lqc_option['color_correct_dark'] ) ) {
			$css .= '--lqc-color-correct-dark:' . $lqc_option['color_correct_dark'] . ';';
		}

		/**
		 * Correct Color (Light)
		 */
		if( !empty( $lqc_option['color_correct_light'] ) ) {
			$css .= '--lqc-color-correct-light:' . $lqc_option['color_correct_light'] . ';';
		}

		/**
		 * Incorrect Color (Dark)
		 */
		if( !empty( $lqc_option['color_incorrect_dark'] ) ) {
			$css .= '--lqc-color-incorrect-dark:' . $lqc_option['color_incorrect_dark'] . ';';
		}

		/**
		 * Incorrect Color (Light)
		 */
		if( !empty( $lqc_option['color_incorrect_light'] ) ) {
			$css .= '--lqc-color-incorrect-light:' . $lqc_option['color_incorrect_light'] . ';';
		}

		/**
		 * Mark Answered Color
		 */
		if( !empty( $lqc_option['color_mark_answered'] ) ) {
			$css .= '--lqc-color-answered:' . $lqc_option['color_mark_answered'] . ';';
		}

		/**
		 * Mark Review Color
		 */
		if( !empty( $lqc_option['color_mark_review'] ) ) {
			$css .= '--lqc-color-review:' . $lqc_option['color_mark_review'] . ';';
		}

		/**
		 * Global Border Radius
		 */
		if( isset( $lqc_option['global_border_radius'] ) && $lqc_option['global_border_radius'] != '' ) {
			$css .= '--lqc-global-border-radius:' . $lqc_option['global_border_radius'] . 'px;';
		}

		/**
		 * Button Border Radius
		 */
		if( isset( $lqc_option['button_border_radius'] ) && $lqc_option['button_border_radius'] != '' ) {
			$css .= '--lqc-button-border-radius:' . $lqc_option['button_border_radius'] . 'px;';
		}

		/**
		 * Primary Button: BG
		 */
		if( !empty( $lqc_option['button_primary_bg_color'] ) ) {
			$css .= '--lqc-button-primary-bg:' . $lqc_option['button_primary_bg_color'] . ';';
		}

		/**
		 * Primary Button: Text
		 */
		if( !empty( $lqc_option['button_primary_text_color'] ) ) {
			$css .= '--lqc-button-primary-text:' . $lqc_option['button_primary_text_color'] . ';';
		}

		/**
		 * Primary Button: BG: Hover
		 */
		if( !empty( $lqc_option['button_primary_bg_color_hover'] ) ) {
			$css .= '--lqc-button-primary-bg-hover:' . $lqc_option['button_primary_bg_color_hover'] . ';';
		}

		/**
		 * Primary Button: Text: Hover
		 */
		if( !empty( $lqc_option['button_primary_text_color_hover'] ) ) {
			$css .= '--lqc-button-primary-text-hover:' . $lqc_option['button_primary_text_color_hover'] . ';';
		}

		/**
		 * Secondary Button: BG
		 */
		if( !empty( $lqc_option['button_secondary_bg_color'] ) ) {
			$css .= '--lqc-button-secondary-bg:' . $lqc_option['button_secondary_bg_color'] . ';';
		}

		/**
		 * Secondary Button: Text
		 */
		if( !empty( $lqc_option['button_secondary_text_color'] ) ) {
			$css .= '--lqc-button-secondary-text:' . $lqc_option['button_secondary_text_color'] . ';';
		}

		/**
		 * Secondary Button: BG: Hover
		 */
		if( !empty( $lqc_option['button_secondary_bg_color_hover'] ) ) {
			$css .= '--lqc-button-secondary-bg-hover:' . $lqc_option['button_secondary_bg_color_hover'] . ';';
		}

		/**
		 * Secondary Button: Text: Hover
		 */
		if( !empty( $lqc_option['button_secondary_text_color_hover'] ) ) {
			$css .= '--lqc-button-secondary-text-hover:' . $lqc_option['button_secondary_text_color_hover'] . ';';
		}

		/**
		 * Review Box: Background
		 */
		if( !empty( $lqc_option['review_box_bg_color'] ) ) {
			$css .= '--lqc-review-box-bg:' . $lqc_option['review_box_bg_color'] . ';';
		}

		/**
		 * Review Box: Border Width
		 */
		if( isset( $lqc_option['review_box_border_width'] ) && $lqc_option['review_box_border_width'] != '' ) {
			$css .= '--lqc-review-box-border-width:' . $lqc_option['review_box_border_width'] . 'px;';
		}

		/**
		 * Review Box: Border Color
		 */
		if( !empty( $lqc_option['review_box_border_color'] ) ) {
			$css .= '--lqc-review-box-border-color:' . $lqc_option['review_box_border_color'] . ';';
		}

		/**
		 * Quiz Timer Container: Background
		 */
		if( !empty( $lqc_option['timer_container_bg_color'] ) ) {
			$css .= '--lqc-timer-container-bg:' . $lqc_option['timer_container_bg_color'] . ';';
		}

		/**
		 * Quiz Timer Container: Text
		 */
		if( !empty( $lqc_option['timer_container_text_color'] ) ) {
			$css .= '--lqc-timer-container-text:' . $lqc_option['timer_container_text_color'] . ';';
		}

		/**
		 * Quiz Timer Container: Border Width
		 */
		if( isset( $lqc_option['timer_container_border_width'] ) && $lqc_option['timer_container_border_width'] != '' ) {
			$css .= '--lqc-timer-container-border-width:' . $lqc_option['timer_container_border_width'] . 'px;';
		}

		/**
		 * Quiz Timer Container: Border Color
		 */
		if( !empty( $lqc_option['timer_container_border_color'] ) ) {
			$css .= '--lqc-timer-container-border-color:' . $lqc_option['timer_container_border_color'] . ';';
		}

		/**
		 * Quiz Timer Bar: Height
		 */
		if( isset( $lqc_option['timer_bar_height'] ) && $lqc_option['timer_bar_height'] != '' ) {
			$css .= '--lqc-timer-bar-height:' . $lqc_option['timer_bar_height'] . 'px;';
		}

		/**
		 * Quiz Timer Bar: Background Color
		 */
		if( !empty( $lqc_option['timer_bar_bg_color'] ) ) {
			$css .= '--lqc-color-timer-bar-bg:' . $lqc_option['timer_bar_bg_color'] . ';';
		}

		/**
		 * Quiz Timer Bar: Color
		 */
		if( !empty( $lqc_option['timer_bar_color'] ) ) {
			$css .= '--lqc-color-timer-bar:' . $lqc_option['timer_bar_color'] . ';';
		}

	// Close :root
	$css .= '}';


	/**
	 * Question Style: Single Choice
	 */
	if( isset( $lqc_option['question_style_single_choice'] ) && $lqc_option['question_style_single_choice'] == 'inline' ) {
		$css .= '@media (min-width:600px){.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type="single"]{display:flex;flex-wrap:wrap;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type="single"] .wpProQuiz_questionListItem{margin:0 0.5em 0.5em 0;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type="single"] label{padding:0.4375em 0.75em;}}';
	}


	/**
	 * Question Style: Multiple Choice
	 */
	if( isset( $lqc_option['question_style_multiple_choice'] ) && $lqc_option['question_style_multiple_choice'] == 'inline' ) {
		$css .= '@media (min-width:600px){.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type="multiple"]{display:flex;flex-wrap:wrap;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type="multiple"] .wpProQuiz_questionListItem{margin:0 0.5em 0.5em 0;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type="multiple"] label{padding:0.4375em 0.75em;}}';
	}


	/**
	 * Question Style: Cloze
	 */

	// Apply styles for ALL types of underlines
	if( $lqc_option['question_style_cloze'] == 'underline_solid' || $lqc_option['question_style_cloze'] == 'underline_dashed' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type=cloze_answer] .wpProQuiz_questionListItem .wpProQuiz_cloze input[type=text]{background:transparent;border:0;border-bottom:1px solid currentColor;border-radius:0;padding:.125em .25em;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type=cloze_answer] .wpProQuiz_questionListItem .wpProQuiz_cloze input[type=text]:hover{background:transparent;border-bottom-color:var(--lqc-color-primary-dark);}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type=cloze_answer] .wpProQuiz_questionListItem .wpProQuiz_cloze input[type=text]:focus{background:transparent;box-shadow:inset 0 -1px 0 0 var(--lqc-color-primary-dark);}';
	}

	// Override solid underline with dashed styles
	if( $lqc_option['question_style_cloze'] == 'underline_dashed' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type=cloze_answer] .wpProQuiz_questionListItem .wpProQuiz_cloze input[type=text]{border-bottom-style:dashed;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_questionList[data-type=cloze_answer] .wpProQuiz_questionListItem .wpProQuiz_cloze input[type=text]:focus{border-bottom-style:solid;}';
	}


	/**
	 * Review Box: Number Style
	 */
	if( isset( $lqc_option['review_numbers_style'] ) && $lqc_option['review_numbers_style'] == 'circular' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewQuestion li,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box li{width:38px;height:38px;padding:2px;border-radius:50%;line-height:34px;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewColor{border-radius:50%;}';
	}
	if( isset( $lqc_option['review_numbers_style'] ) && $lqc_option['review_numbers_style'] == 'square' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewQuestion li,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box li{width:38px;height:38px;padding:2px;border-radius:0;line-height:34px;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewColor{border-radius:0;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewDiv .wpProQuiz_reviewQuestion,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box{border-radius:0;}';
	}


	/**
	 * Quiz Timer Bar: Style (Solid/Striped)
	 */
	if( isset( $lqc_option['timer_bar_style'] ) && $lqc_option['timer_bar_style'] == 'striped' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit .wpProQuiz_progress,.lqc-plugin .learndash .wpProQuiz_content .sending_progress_bar{background-image:linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);background-size:1em 1em;}';
	}


	/**
	 * Sticky: Stick to Top
	 */
	if( isset( $lqc_option['stick_to_top'] ) && $lqc_option['stick_to_top'] == 'question_xofy' ) {
		$css .= '.lqc-plugin .wpProQuiz_content .wpProQuiz_question_page{position:fixed;top:0;left:0;width:100%;padding:4px 0.5em;margin:0;background:#fff;border-bottom:1px solid rgba(0,0,0,0.1);z-index:999;}.lqc-plugin .learndash .wpProQuiz_content .lqc-question-list-2{margin:0;padding:0;font-size:16px;height:26px;line-height:26px;}body.lqc-plugin.single-sfwd-quiz{padding-top:35px !important;}';
	}

	if( $lqc_option['stick_to_top'] == 'review_area_1' || $lqc_option['stick_to_top'] == 'review_area_2' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewDiv .wpProQuiz_reviewQuestion,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box{position:fixed;top:0;left:0;width:100%;overflow-y:scroll;padding:0;margin:0;border-radius:0;border:0;border-bottom:2px solid var(--lqc-review-box-border-color);z-index:999;}';

		// 1 Row
		if( isset( $lqc_option['stick_to_top'] ) && $lqc_option['stick_to_top'] == 'review_area_1' ) {
			$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewDiv .wpProQuiz_reviewQuestion,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box{height:50px;}body.lqc-plugin.single-sfwd-quiz{padding-top:50px !important;}';
		}

		// 2 Rows
		if( isset( $lqc_option['stick_to_top'] ) && $lqc_option['stick_to_top'] == 'review_area_2' ) {
			$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewDiv .wpProQuiz_reviewQuestion,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box{height:94px;}body.lqc-plugin.single-sfwd-quiz{padding-top:94px !important;}';
		}

		// Sticky: Max Width
		if( isset( $lqc_option['sticky_max_width'] ) && $lqc_option['sticky_max_width'] != '' ) {
			$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_reviewQuestion ol,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_box ol{max-width:' . $lqc_option['sticky_max_width'] . 'px;margin:0 auto !important;}';
		}

	} // review_area

	/**
	 * Sticky: Stick to Bottom
	 */
	// Time Limit: ALL
	if( $lqc_option['stick_to_bottom'] == 'time_limit_full' || $lqc_option['stick_to_bottom'] == 'time_limit_small_bar' || $lqc_option['stick_to_bottom'] == 'time_limit_text_only' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit{position:fixed;bottom:0;left:0;width:100%;margin:0;border-radius:0;z-index:999;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit::before{bottom:0.5em;left:0.5em;right:0.5em;}';
	}

	// Time Limit: Full -OR- Text Only
	if( $lqc_option['stick_to_bottom'] == 'time_limit_full' || $lqc_option['stick_to_bottom'] == 'time_limit_text_only' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit{padding:0.5em;border:0;border-top:2px solid var(--lqc-timer-container-border-color);}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit::before{bottom:0.5em;left:0.5em;right:0.5em;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit .time{text-align:center;}body.lqc-plugin.single-sfwd-quiz{padding-bottom:50px !important;}';
	}

	// Time Limit: Text Only
	if( isset( $lqc_option['stick_to_bottom'] ) && $lqc_option['stick_to_bottom'] == 'time_limit_text_only' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit::before,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit .wpProQuiz_progress{display:none;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit .time{margin:0;}body.lqc-plugin.single-sfwd-quiz{padding-bottom:30px !important;}';
	}

	// Time Limit: Small Bar
	if( isset( $lqc_option['stick_to_bottom'] ) && $lqc_option['stick_to_bottom'] == 'time_limit_small_bar' ) {
		$css .= '.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit{padding:0;border:0;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit::before,.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit .wpProQuiz_progress{height:6px !important;border-radius:0;bottom:0;left:0;right:0;}.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_time_limit .time{display:none;}body.lqc-plugin.single-sfwd-quiz{padding-bottom:6px !important;}';
	}

	// Navigation Buttons
	if( isset( $lqc_option['stick_to_bottom'] ) && $lqc_option['stick_to_bottom'] == 'quiz_nav_buttons' ) {
		$css .= '@media (max-width:800px){.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=check],.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=next],.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=back],.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=tip],.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=skip]{position:fixed;bottom:0;border:6px solid white;width:33.3333%;margin-bottom:0;z-index:999;font-size:73%;} .lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=check],.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=next]{right:0;} .lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=back],.lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=skip]{left:0;} .lqc-plugin .learndash .wpProQuiz_content .wpProQuiz_button[name=tip]{left:31%;}}';
	}

	// Print CSS
	return $css;

} // lqc_learndash_quiz_customizer_inline_css
	// this is the function that creates all the inline CSS styles