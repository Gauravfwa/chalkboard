<?php

/**
 * Implements Customizer functionality.
 *
 * Add custom sections and settings to the Customizer.
 *
 * @package   lqc-learndash-quiz-customizer
 * @copyright Copyright (c) 2019, Escape Creative, LLC
 * @license   GPL2+
 */
class LQC_Learndash_Quiz_Customizer_Setup {

	/**
	 * LQC_Learndash_Quiz_Customizer_Setup constructor.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {

		add_action( 'customize_register', array( $this, 'lqc_register_customize_sections' ) );

	}

	/**
	 * Add all sections and panels to the Customizer
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function lqc_register_customize_sections( $wp_customize ) {

		/**
		 * Add Panels
		 */

		// Panel containing all Quiz sub-sections
		$wp_customize->add_panel( 'lqc_learndash_quiz_panel', array(
			'title' => 'Quiz Customizer for LearnDash',
			'description' => __( 'These styles only apply to LearnDash quiz pages. Use the preview window to visit a page that contains a LearnDash quiz.', 'learndash-quiz-customizer' ),
			'priority' => 161
		) );

		// Text: Buttons
		$wp_customize->add_section( 'lqc_quiz_text_buttons', array(
			'title'    => __( 'Text: Buttons', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 110
		) );

		// Text: Labels
		$wp_customize->add_section( 'lqc_quiz_text_labels', array(
			'title'    => __( 'Text: Labels', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 120
		) );

		// Text: Messages
		$wp_customize->add_section( 'lqc_quiz_text_messages', array(
			'title'    => __( 'Text: Messages', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 130
		) );

		// Design: Global
		$wp_customize->add_section( 'lqc_quiz_design_global', array(
			'title'    => __( 'Design: Global', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 140
		) );

		// Design: Colors
		$wp_customize->add_section( 'lqc_quiz_design_colors', array(
			'title'    => __( 'Design: Colors', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 150
		) );

		// Design: Buttons
		$wp_customize->add_section( 'lqc_quiz_design_buttons', array(
			'title'    => __( 'Design: Buttons', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 160
		) );

		// Question Style
		$wp_customize->add_section( 'lqc_quiz_question_style', array(
			'title'    => sprintf( esc_html_x( '%s Style', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 170
		) );

		// Review Box
		$wp_customize->add_section( 'lqc_quiz_review_box', array(
			'title'    => __( 'Review Box', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 180
		) );

		// Quiz Timer
		$wp_customize->add_section( 'lqc_quiz_timer', array(
			'title'    => __( 'Timer', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 190
		) );

		// Sticky Elements
		$wp_customize->add_section( 'lqc_quiz_sticky', array(
			'title'    => __( 'Sticky Elements', 'learndash-quiz-customizer' ),
			'description' => __( '<b>Experimental:</b> These options should work, but may not look great on all themes & setups.', 'learndash-quiz-customizer' ),
			'panel'    => 'lqc_learndash_quiz_panel',
			'priority' => 200
		) );

		/*
		 * Add settings to sections.
		 */
		$this->lqc_quiz_text_buttons_section( $wp_customize );
		$this->lqc_quiz_text_labels_section( $wp_customize );
		$this->lqc_quiz_text_messages_section( $wp_customize );
		$this->lqc_quiz_design_global_section( $wp_customize );
		$this->lqc_quiz_design_colors_section( $wp_customize );
		$this->lqc_quiz_design_buttons_section( $wp_customize );
		$this->lqc_quiz_question_style_section( $wp_customize );
		$this->lqc_quiz_review_box_section( $wp_customize );
		$this->lqc_quiz_timer_section( $wp_customize );
		$this->lqc_quiz_sticky_section( $wp_customize );

	}

	/**
	 * Section: Text: Buttons
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_text_buttons_section( $wp_customize ) {

		/**
		 * Start Quiz
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_start_quiz]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_start_quiz]', array(
			'type'     => 'text',
			'label'    => sprintf( esc_html_x( 'Start %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'Start %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_start_quiz]',
			'priority' => 10
		) );

		/**
		 * Finish Quiz
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_finish_quiz]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_finish_quiz]', array(
			'type'     => 'text',
			'label'    => sprintf( esc_html_x( 'Finish %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'Finish %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_finish_quiz]',
			'priority' => 15
		) );

		/**
		 * Restart Quiz
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_restart_quiz]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_restart_quiz]', array(
			'type'     => 'text',
			'label'    => sprintf( esc_html_x( 'Restart %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'Restart %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_restart_quiz]',
			'priority' => 20
		) );

		/**
		 * Next
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_next_question]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_next_question]', array(
			'type'     => 'text',
			'label'    => __( 'Next', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Next', 'learndash-quiz-customizer' ),
				'style'       => 'max-width: 220px; display: block;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_next_question]',
			'priority' => 25
		) );

		/**
		 * Back
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_back_question]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_back_question]', array(
			'type'     => 'text',
			'label'    => __( 'Back', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Back', 'learndash-quiz-customizer' ),
				'style'       => 'max-width: 220px; display: block;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_back_question]',
			'priority' => 30
		) );

		/**
		 * Check
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_check_question]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_check_question]', array(
			'type'     => 'text',
			'label'    => __( 'Check', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Check', 'learndash-quiz-customizer' ),
				'style'       => 'max-width: 220px; display: block;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_check_question]',
			'priority' => 35
		) );

		/**
		 * Hint
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_hint_question]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_hint_question]', array(
			'type'     => 'text',
			'label'    => __( 'Hint', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Hint', 'learndash-quiz-customizer' ),
				'style'       => 'max-width: 220px; display: block;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_hint_question]',
			'priority' => 40
		) );

		/**
		 * Skip Question
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_skip_question]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_skip_question]', array(
			'type'     => 'text',
			'label'    => sprintf( esc_html_x( 'Skip %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'Skip %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_skip_question]',
			'priority' => 45
		) );

		/**
		 * Review Question
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_review_question]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_review_question]', array(
			'type'     => 'text',
			'label'    => sprintf( esc_html_x( 'Review %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'Review %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_review_question]',
			'priority' => 50
		) );

		/**
		 * Quiz Summary
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_quiz_summary]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_quiz_summary]', array(
			'type'     => 'text',
			'label'    => sprintf( esc_html_x( '%s Summary', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( '%s Summary', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_quiz_summary]',
			'priority' => 55
		) );

		/**
		 * View Questions
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_view_questions]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_view_questions]', array(
			'type'     => 'text',
			'label'    =>  sprintf( esc_html_x( 'View %s', 'placeholder: Questions', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'questions' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'View %s', 'placeholder: Questions', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'questions' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_view_questions]',
			'priority' => 60
		) );

		/**
		 * View Quiz Statistics
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_view_quiz_stats]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_view_quiz_stats]', array(
			'type'     => 'text',
			'label'    =>  sprintf( esc_html_x( 'View %s Statistics', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( 'View %s Statistics', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_view_quiz_stats]',
			'priority' => 65
		) );

		/**
		 * Show Leaderboard
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_show_leaderboard]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_show_leaderboard]', array(
			'type'     => 'text',
			'label'    =>  __( 'Show Leaderboard', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Show Leaderboard', 'learndash-quiz-customizer' ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_show_leaderboard]',
			'priority' => 70
		) );

		/**
		 * Print Certificate
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[btn_print_certificate]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[btn_print_certificate]', array(
			'type'     => 'text',
			'label'    =>  __( 'Print Certificate', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Print Your Certificate', 'learndash-quiz-customizer' ),
				'style'       => 'max-width: 220px;'
			),
			'section'  => 'lqc_quiz_text_buttons',
			'settings' => 'lqc_quiz_options[btn_print_certificate]',
			'priority' => 75
		) );

	} // function lqc_quiz_text_buttons_section( $wp_customize )


	/**
	 * Section: Text: Labels
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_text_labels_section( $wp_customize ) {

		/**
		 * X Point(s)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_x_points]', array(
			'type'              => 'option',
			'default'           => 'one',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_x_points]', array(
			'type'     => 'select',
			'label'    =>  __( '1 Point(s)', 'learndash-quiz-customizer' ),
			'choices'  => array(
				'one'   => __( '1 Point(s)', 'learndash-quiz-customizer' ),
				'two'   => __( 'Points: 1', 'learndash-quiz-customizer' ),
				'three' => __( 'Points = 1', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_x_points]',
			'priority' => 10
		) );

		/**
		 * Question X of Y
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_question_list_2]', array(
			'type'              => 'option',
			'default'           => 'one',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_question_list_2]', array(
			'type'     => 'select',
			'label'    =>  sprintf( esc_html_x( '%s 1 of 5', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
			'choices'  => array(
				'one'   => sprintf( esc_html_x( '%s 1 of 5', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'two'   => sprintf( esc_html_x( '%s 1 / 5', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'three' => sprintf( esc_html_x( '%s 1/5', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'four'  => __( '1 of 5', 'learndash-quiz-customizer' ),
				'five'  => __( '1 / 5', 'learndash-quiz-customizer' ),
				'six'   => __( '1/5', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_question_list_2]',
			'priority' => 15
		) );

		/**
		 * X. Question
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_question_list_1]', array(
			'type'              => 'option',
			'default'           => 'one',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_question_list_1]', array(
			'type'     => 'select',
			'label'    =>  sprintf( esc_html_x( '1. %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
			'choices'     => array(
				'one'   => sprintf( esc_html_x( '1. %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'two'   => sprintf( esc_html_x( '1) %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'three' => sprintf( esc_html_x( '1: %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'four'  => sprintf( esc_html_x( '1 - %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'five'  => sprintf( esc_html_x( '%s 1', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'six'   => sprintf( esc_html_x( '%s #1', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) ),
				'seven' => LearnDash_Custom_Label::get_label( 'question' ),
				'eight' => __( '1', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_question_list_1]',
			'priority' => 20
		) );

		/**
		 * Quiz Summary
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_quiz_summary]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_quiz_summary]', array(
			'type'     => 'text',
			'label'    =>  sprintf( esc_html_x( '%s Summary', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'placeholder' => sprintf( esc_html_x( '%s Summary', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_quiz_summary]',
			'priority' => 25
		) );

		/**
		 * Answered
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_answered]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_answered]', array(
			'type'     => 'text',
			'label'    =>  __( 'Answered', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Answered', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_answered]',
			'priority' => 30
		) );

		/**
		 * Review
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_review]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_review]', array(
			'type'     => 'text',
			'label'    =>  __( 'Review', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Review', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_review]',
			'priority' => 35
		) );

		/**
		 * Sort Elements
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_sort_elements]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_sort_elements]', array(
			'type'     => 'text',
			'label'    =>  sprintf( esc_html_x( 'Sort Elements Header (Matrix %s)', 'placeholder: Questions', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'questions' ) ),
			'input_attrs' => array(
				'placeholder' => __( '(hidden)', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_sort_elements]',
			'priority' => 40
		) );

		/**
		 * Hint (heading label)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_hint]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_hint]', array(
			'type'     => 'text',
			'label'    =>  __( 'Hint', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_hint]',
			'priority' => 45
		) );

		/**
		 * Correct
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_correct]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_correct]', array(
			'type'     => 'text',
			'label'    =>  __( 'Correct', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Correct', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_correct]',
			'priority' => 50
		) );

		/**
		 * Incorrect
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_incorrect]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_incorrect]', array(
			'type'     => 'text',
			'label'    =>  __( 'Incorrect', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Incorrect', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_incorrect]',
			'priority' => 55
		) );

		/**
		 * Average Score
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_average_score]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_average_score]', array(
			'type'     => 'text',
			'label'    =>  __( 'Average Score', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Average Score', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_average_score]',
			'priority' => 60
		) );

		/**
		 * Your Score
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_your_score]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_your_score]', array(
			'type'     => 'text',
			'label'    =>  __( 'Your Score', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Your Score', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_your_score]',
			'priority' => 65
		) );

		/**
		 * Your Time: 00:00:00
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_your_time]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_your_time]', array(
			'type'     => 'text',
			'label'    =>  __( 'Your Time:', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Your Time:', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_your_time]',
			'priority' => 67
		) );

		/**
		 * Earned Points: X of Y
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_earned_points]', array(
			'type'              => 'option',
			'default'           => 'one',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_earned_points]', array(
			'type'     => 'select',
			'label'    =>  __( 'Earned Points: 1 of 5 (20%)', 'learndash-quiz-customizer' ),
			'choices'     => array(
				'one'   => __( 'Earned Points: 1 of 5 (20%)', 'learndash-quiz-customizer' ),
				'two'   => __( 'Earned Points: 1 of 5', 'learndash-quiz-customizer' ),
				'three' => __( 'Points Earned: 1 of 5 (20%)', 'learndash-quiz-customizer' ),
				'four'  => __( 'Points Earned: 1 of 5', 'learndash-quiz-customizer' ),
				'five'  => __( 'Score: 1 of 5 (20%)', 'learndash-quiz-customizer' ),
				'six'   => __( 'Grade: 20%', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_earned_points]',
			'priority' => 70
		) );

		/**
		 * Essays Pending
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_essays_pending]', array(
			'type'              => 'option',
			'default'           => 'one',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_essays_pending]', array(
			'type'     => 'select',
			'label'    =>  __( 'Essay(s) Pending: (Possible Points)', 'learndash-quiz-customizer' ),
			'choices'     => array(
				'one'   => __( '2 Essays Pending (Possible Points: 10)', 'learndash-quiz-customizer' ),
				'two'   => __( '2 Essays Pending (10 Possible Points)', 'learndash-quiz-customizer' ),
				'three' => __( '2 Essays Pending', 'learndash-quiz-customizer' ),
				'four'  => __( 'Essays Pending: 2', 'learndash-quiz-customizer' ),
				'five'  => __( 'Essays Pending: 2, Possible Points: 10', 'learndash-quiz-customizer' ),
				'six'   => __( '10 points awaiting a grade.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_essays_pending]',
			'priority' => 75
		) );

		/**
		 * Not Categorized
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_category_x]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_category_x]', array(
			'type'     => 'text',
			'label'    =>  __( 'Category:', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Category:', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_category_x]',
			'priority' => 80
		) );

		/**
		 * Not Categorized
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_not_categorized]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_not_categorized]', array(
			'type'     => 'text',
			'label'    =>  __( 'Not Categorized', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Not Categorized', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_not_categorized]',
			'priority' => 85
		) );

		/**
		 * Categories (heading)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[label_categories_header]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[label_categories_header]', array(
			'type'     => 'text',
			'label'    =>  __( 'Categories', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'placeholder' => __( 'Categories', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_labels',
			'settings' => 'lqc_quiz_options[label_categories_header]',
			'priority' => 90
		) );

	} // function lqc_quiz_text_labels_section( $wp_customize )


	/**
	 * Section: Text: Messages
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_text_messages_section( $wp_customize ) {

		/**
		 * Quiz Locked
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_quiz_locked]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_quiz_locked]', array(
			'type'     => 'textarea',
			'label'    =>  sprintf( esc_html_x( '%s Locked', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => sprintf( esc_html_x( 'You have already completed this %s. You cannot start it again.', 'placeholder: quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_quiz_locked]',
			'priority' => 10
		) );

		/**
		 * Quiz Registered Users Only
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_registered_user_only]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_registered_user_only]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Registered Users Only', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => sprintf( esc_html_x( 'You must sign in or sign up to take this %s.', 'placeholder: quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_registered_user_only]',
			'priority' => 15
		) );

		/**
		 * Quiz Prerequisite
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_quiz_prerequisite]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_quiz_prerequisite]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Prerequisite Required', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'You must first complete the following:', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_quiz_prerequisite]',
			'priority' => 20
		) );

		/**
		 * Quiz Complete
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_quiz_complete]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_quiz_complete]', array(
			'type'     => 'textarea',
			'label'    =>  sprintf( esc_html_x( '%s Complete', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => sprintf( esc_html_x( '%s complete. Results are being recorded.', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_quiz_complete]',
			'priority' => 25
		) );

		/**
		 * Time Elapsed
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_time_elapsed]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_time_elapsed]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Time Elapsed', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Time has elapsed.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_time_elapsed]',
			'priority' => 30
		) );

		/**
		 * Leaderboard: Intro Message
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_leaderboard_intro]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_leaderboard_intro]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Leaderboard: Intro Message', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Would you like to add your score to the leaderboard?', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_leaderboard_intro]',
			'priority' => 35
		) );

		/**
		 * Essay: Upload Your Answer
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_essay_upload_answer]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_essay_upload_answer]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Essay: Upload Your Answer', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => sprintf( esc_html_x( 'Upload your answer to this %s.', 'placeholder: question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'question' ) )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_essay_upload_answer]',
			'priority' => 40
		) );

		/**
		 * Essay: Graded, Full Points Awarded
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_essay_graded_full]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_essay_graded_full]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Essay: Graded, Full Points Awarded', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Your response will be awarded full points automatically, but it can be reviewed and adjusted after submission.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_essay_graded_full]',
			'priority' => 45
		) );

		/**
		 * Essay: Not Graded, Full Points Awarded
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_essay_notgraded_full]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_essay_notgraded_full]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Essay: Not Graded, Full Points Awarded', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Your response will be awarded full points automatically, but it will be reviewed and possibly adjusted after submission.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_essay_notgraded_full]',
			'priority' => 50
		) );

		/**
		 * Essay: Not Graded, No Points Awarded
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_essay_notgraded_none]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_essay_notgraded_none]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Essay: Not Graded, No Points Awarded', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Your response will be reviewed and graded after submission.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_essay_notgraded_none]',
			'priority' => 55
		) );

		/**
		 * Essay: Graded, Review
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_essay_graded_review]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_essay_graded_review]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Essay: Graded, Review', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Your grade may be reviewed and adjusted.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_essay_graded_review]',
			'priority' => 60
		) );

		/**
		 * Essay: Textarea Placeholder
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_essay_textarea_placeholder]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_essay_textarea_placeholder]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Essay: Textarea Placeholder', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => __( 'Type your response here.', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_essay_textarea_placeholder]',
			'priority' => 65
		) );

		/**
		 * Certificate Pending
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[message_certificate_pending]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_textarea_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[message_certificate_pending]', array(
			'type'     => 'textarea',
			'label'    =>  __( 'Certificate Pending', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'style'       => 'height: 70px;',
				'placeholder' => sprintf( esc_html_x( 'Certificate Pending: Some %s still need to be graded.', 'placeholder: questions', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'questions' ) )
			),
			'section'  => 'lqc_quiz_text_messages',
			'settings' => 'lqc_quiz_options[message_certificate_pending]',
			'priority' => 70
		) );

	} // function lqc_quiz_text_messages_section( $wp_customize )


	/**
	 * Section: Design: Global
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_design_global_section( $wp_customize ) {

		/**
		 * Global Border Radius
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[global_border_radius]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[global_border_radius]', array(
			'type'     => 'number',
			'label'    =>  __( 'Global Border Radius', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'min'   => 1,
				'max'   => 100,
				'style' => 'width: 60px; display: block;'
			),
			'description' => __( 'Value is in pixels (px).', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_global',
			'settings' => 'lqc_quiz_options[global_border_radius]',
			'priority' => 10
		) );

	} // function lqc_quiz_design_global_section( $wp_customize )


	/**
	 * Section: Design: Colors
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_design_colors_section( $wp_customize ) {

		/**
		 * Primary Color (Dark)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_primary_dark]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_primary_dark]', array(
			'label'    => __( 'Primary Color (Dark)', 'learndash-quiz-customizer' ),
			'description' => __( 'Mainly used when selecting answers. <br />Default #00a2e8', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_colors',
			'settings' => 'lqc_quiz_options[color_primary_dark]',
			'priority' => 10
		) ) );

		/**
		 * Primary Color (Light)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_primary_light]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_primary_light]', array(
			'label'    => __( 'Primary Color (Light)', 'learndash-quiz-customizer' ),
			'description' => __( 'Should be a subtle, much lighter version of the above. Default #e0f5fe', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_colors',
			'settings' => 'lqc_quiz_options[color_primary_light]',
			'priority' => 15
		) ) );

		/**
		 * Correct Color (Dark)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_correct_dark]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_correct_dark]', array(
			'label'    => __( 'Correct Color (Dark)', 'learndash-quiz-customizer' ),
			'description' => __( 'Mainly used for correct answers. <br />Default #019e7c', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_colors',
			'settings' => 'lqc_quiz_options[color_correct_dark]',
			'priority' => 20
		) ) );

		/**
		 * Correct Color (Light)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_correct_light]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_correct_light]', array(
			'label'    => __( 'Correct Color (Light)', 'learndash-quiz-customizer' ),
			'description' => __( 'Should be a subtle, much lighter version of the above. Default #c8e6c9', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_colors',
			'settings' => 'lqc_quiz_options[color_correct_light]',
			'priority' => 25
		) ) );

		/**
		 * Incorrect Color (Dark)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_incorrect_dark]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_incorrect_dark]', array(
			'label'    => __( 'Incorrect Color (Dark)', 'learndash-quiz-customizer' ),
			'description' => __( 'Mainly used for incorrect answers. <br />Default #c62828', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_colors',
			'settings' => 'lqc_quiz_options[color_incorrect_dark]',
			'priority' => 30
		) ) );

		/**
		 * Incorrect Color (Light)
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_incorrect_light]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_incorrect_light]', array(
			'label'    => __( 'Incorrect Color (Light)', 'learndash-quiz-customizer' ),
			'description' => __( 'Should be a subtle, much lighter version of the above. Default #ffcdd2', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_colors',
			'settings' => 'lqc_quiz_options[color_incorrect_light]',
			'priority' => 35
		) ) );

	} // function lqc_quiz_design_colors_section( $wp_customize )


	/**
	 * Section: Design: Buttons
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_design_buttons_section( $wp_customize ) {

		/**
		 * Button Border Radius
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_border_radius]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[button_border_radius]', array(
			'type'     => 'number',
			'label'    =>  __( 'Button Border Radius', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'min'   => 1,
				'max'   => 100,
				'style' => 'width: 60px; display: block;'
			),
			'description' => __( 'Value is in pixels (px).', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_border_radius]',
			'priority' => 10
		) );

		/**
		 * Primary Button: BG Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_primary_bg_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_primary_bg_color]', array(
			'label'    => __( 'Primary Button: Background', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_primary_bg_color]',
			'priority' => 20
		) ) );

		/**
		 * Primary Button: Text Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_primary_text_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_primary_text_color]', array(
			'label'    => __( 'Primary Button: Text', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_primary_text_color]',
			'priority' => 25
		) ) );

		/**
		 * Primary Button: Hover: BG Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_primary_bg_color_hover]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_primary_bg_color_hover]', array(
			'label'    => __( 'Primary Button: Hover: Background', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_primary_bg_color_hover]',
			'priority' => 30
		) ) );

		/**
		 * Primary Button: Hover: Text Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_primary_text_color_hover]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_primary_text_color_hover]', array(
			'label'    => __( 'Primary Button: Hover: Text', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_primary_text_color_hover]',
			'priority' => 35
		) ) );

		/**
		 * Secondary Button: BG Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_secondary_bg_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_secondary_bg_color]', array(
			'label'    => __( 'Secondary Button: Background', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_secondary_bg_color]',
			'priority' => 40
		) ) );

		/**
		 * Secondary Button: Text Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_secondary_text_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_secondary_text_color]', array(
			'label'    => __( 'Secondary Button: Text', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_secondary_text_color]',
			'priority' => 45
		) ) );

		/**
		 * Secondary Button: Hover: BG Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_secondary_bg_color_hover]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_secondary_bg_color_hover]', array(
			'label'    => __( 'Secondary Button: Hover: Background', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_secondary_bg_color_hover]',
			'priority' => 50
		) ) );

		/**
		 * Secondary Button: Hover: Text Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[button_secondary_text_color_hover]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[button_secondary_text_color_hover]', array(
			'label'    => __( 'Secondary Button: Hover: Text', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_design_buttons',
			'settings' => 'lqc_quiz_options[button_secondary_text_color_hover]',
			'priority' => 55
		) ) );

	} // function lqc_quiz_design_buttons_section( $wp_customize )


	/**
	 * Section: Question Style
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_question_style_section( $wp_customize ) {

		/**
		 * Question Style: Single Choice
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[question_style_single_choice]', array(
			'type'              => 'option',
			'default'           => 'stacked',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[question_style_single_choice]', array(
			'type'     => 'select',
			'label'    =>  __( 'Single Choice', 'learndash-quiz-customizer' ),
			'choices'  => array(
				'stacked'   => __( 'Stacked (default)', 'learndash-quiz-customizer' ),
				'inline'   => __( 'Inline', 'learndash-quiz-customizer' )
			),
			'description' => __( '<b>Inline</b> works on screens 600px wide & up.', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_question_style',
			'settings' => 'lqc_quiz_options[question_style_single_choice]',
			'priority' => 10
		) );

		/**
		 * Question Style: Multiple Choice
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[question_style_multiple_choice]', array(
			'type'              => 'option',
			'default'           => 'stacked',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[question_style_multiple_choice]', array(
			'type'     => 'select',
			'label'    =>  __( 'Multiple Choice', 'learndash-quiz-customizer' ),
			'choices'  => array(
				'stacked'   => __( 'Stacked (default)', 'learndash-quiz-customizer' ),
				'inline'   => __( 'Inline', 'learndash-quiz-customizer' )
			),
			'description' => __( '<b>Inline</b> works on screens 600px wide & up.', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_question_style',
			'settings' => 'lqc_quiz_options[question_style_multiple_choice]',
			'priority' => 15
		) );

		/**
		 * Question Style: Fill in the Blank
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[question_style_cloze]', array(
			'type'              => 'option',
			'default'           => 'background',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[question_style_cloze]', array(
			'type'     => 'select',
			'label'    =>  __( 'Fill in the Blank', 'learndash-quiz-customizer' ),
			'choices'  => array(
				'background'       => __( 'Background (default)', 'learndash-quiz-customizer' ),
				'underline_solid'  => __( 'Underline, Solid', 'learndash-quiz-customizer' ),
				'underline_dashed' => __( 'Underline, Dashed', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_question_style',
			'settings' => 'lqc_quiz_options[question_style_cloze]',
			'priority' => 20
		) );

	} // function lqc_quiz_question_style_section( $wp_customize )


	/**
	 * Section: Review Box
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_review_box_section( $wp_customize ) {

		/**
		 * Review Box Container: Background Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[review_box_bg_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[review_box_bg_color]', array(
			'label'    => __( 'Container: Background', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_review_box',
			'settings' => 'lqc_quiz_options[review_box_bg_color]',
			'priority' => 1
		) ) );

		/**
		 * Review Box Container: Border Width
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[review_box_border_width]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[review_box_border_width]', array(
			'type'     => 'number',
			'label'    =>  __( 'Container: Border Width', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'min'   => 1,
				'max'   => 100,
				'style' => 'width: 60px; display: block;'
			),
			'description' => __( 'Value is in pixels (px).', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_review_box',
			'settings' => 'lqc_quiz_options[review_box_border_width]',
			'priority' => 2
		) );

		/**
		 * Review Box Container: Border Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[review_box_border_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[review_box_border_color]', array(
			'label'    => __( 'Container: Border Color', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_review_box',
			'settings' => 'lqc_quiz_options[review_box_border_color]',
			'priority' => 3
		) ) );

		/**
		 * Review Box: Number Style
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[review_numbers_style]', array(
			'type'              => 'option',
			'default'           => 'default',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[review_numbers_style]', array(
			'type'     => 'select',
			'label'    =>  __( 'Number Style', 'learndash-quiz-customizer' ),
			'description' => __( 'Default inherits global border radius.', 'learndash-quiz-customizer' ),
			'choices'  => array(
				'default'   => __( 'Default', 'learndash-quiz-customizer' ),
				'circular'   => __( 'Circular', 'learndash-quiz-customizer' ),
				'square'   => __( 'Square', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_review_box',
			'settings' => 'lqc_quiz_options[review_numbers_style]',
			'priority' => 10
		) );

		/**
		 * Mark Answered Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_mark_answered]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_mark_answered]', array(
			'label'    => __( 'Mark Answered', 'learndash-quiz-customizer' ),
			'description' => __( 'Default #6ca54c', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_review_box',
			'settings' => 'lqc_quiz_options[color_mark_answered]',
			'priority' => 20
		) ) );

		/**
		 * Mark Review Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[color_mark_review]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[color_mark_review]', array(
			'label'    => __( 'Mark For Review', 'learndash-quiz-customizer' ),
			'description' => __( 'Default #ffb800', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_review_box',
			'settings' => 'lqc_quiz_options[color_mark_review]',
			'priority' => 25
		) ) );

	} // function lqc_quiz_review_box_section( $wp_customize )


	/**
	 * Section: Quiz Timer
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_timer_section( $wp_customize ) {

		/**
		 * Timer Container: Background Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_container_bg_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[timer_container_bg_color]', array(
			'label'    => __( 'Container: Background', 'learndash-quiz-customizer' ),
			'description' => __( 'Default #f0f3f6', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_container_bg_color]',
			'priority' => 1
		) ) );

		/**
		 * Timer Container: Text Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_container_text_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[timer_container_text_color]', array(
			'label'    => __( 'Container: Text', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_container_text_color]',
			'priority' => 2
		) ) );

		/**
		 * Timer Container: Border Width
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_container_border_width]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[timer_container_border_width]', array(
			'type'     => 'number',
			'label'    =>  __( 'Container: Border Width', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'min'   => 1,
				'max'   => 100,
				'style' => 'width: 60px; display: block;'
			),
			'description' => __( 'Value is in pixels (px).', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_container_border_width]',
			'priority' => 3
		) );

		/**
		 * Timer Container: Border Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_container_border_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[timer_container_border_color]', array(
			'label'    => __( 'Container: Border Color', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_container_border_color]',
			'priority' => 4
		) ) );

		/**
		 * Quiz Timer: Bar Style
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_bar_style]', array(
			'type'              => 'option',
			'default'           => 'solid',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[timer_bar_style]', array(
			'type'     => 'select',
			'label'    =>  __( 'Bar Style', 'learndash-quiz-customizer' ),
			'choices'  => array(
				'solid'     => __( 'Solid', 'learndash-quiz-customizer' ),
				'striped'   => __( 'Striped', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_bar_style]',
			'priority' => 10
		) );

		/**
		 * Quiz Timer: Bar Height
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_bar_height]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[timer_bar_height]', array(
			'type'     => 'number',
			'label'    =>  __( 'Bar Height', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'min'   => 1,
				'max'   => 100,
				'style' => 'width: 60px; display: block;'
			),
			'description' => __( 'Value is in pixels (px).', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_bar_height]',
			'priority' => 20
		) );

		/**
		 * Bar Background Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_bar_bg_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[timer_bar_bg_color]', array(
			'label'    => __( 'Bar Background', 'learndash-quiz-customizer' ),
			'description' => __( 'Default #ffffff', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_bar_bg_color]',
			'priority' => 30
		) ) );

		/**
		 * Bar Background Color
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[timer_bar_color]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'lqc_quiz_options[timer_bar_color]', array(
			'label'    => __( 'Bar Color', 'learndash-quiz-customizer' ),
			'description' => __( 'Default inherits dark primary color.', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_timer',
			'settings' => 'lqc_quiz_options[timer_bar_color]',
			'priority' => 40
		) ) );

	} // function lqc_quiz_timer_section( $wp_customize )


	/**
	 * Section: Sticky Elements
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @access private
	 * @since  1.0
	 * @return void
	 */
	private function lqc_quiz_sticky_section( $wp_customize ) {

		/**
		 * Sticky: Stick to Top
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[stick_to_top]', array(
			'type'              => 'option',
			'default'           => 'disabled',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[stick_to_top]', array(
			'type'        => 'select',
			'label'       =>  __( 'Stick to Top', 'learndash-quiz-customizer' ),
			'description' =>  __( 'Does not work well with Focus Mode on desktop.', 'learndash-quiz-customizer' ),
			'choices'     => array(
				'disabled'          => __( '-Disabled-', 'learndash-quiz-customizer' ),
				'question_xofy'     => __( 'Question X of Y', 'learndash-quiz-customizer' ),
				'review_area_1'     => __( 'Review Area, 1 Row', 'learndash-quiz-customizer' ),
				'review_area_2'     => __( 'Review Area, 2 Rows', 'learndash-quiz-customizer' )
			),
			'section'  => 'lqc_quiz_sticky',
			'settings' => 'lqc_quiz_options[stick_to_top]',
			'priority' => 10
		) );

		/**
		 * Sticky: Stick to Bottom
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[stick_to_bottom]', array(
			'type'              => 'option',
			'default'           => 'disabled',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[stick_to_bottom]', array(
			'type'        => 'select',
			'label'       => __( 'Stick to Bottom', 'learndash-quiz-customizer' ),
			'description' => __( 'Navigation buttons work on screens below 800px.', 'learndash-quiz-customizer' ),
			'choices'     => array(
				'disabled'             => __( '-Disabled-', 'learndash-quiz-customizer' ),
				'quiz_nav_buttons'     => __( 'Navigation Buttons', 'learndash-quiz-customizer' ),
				'time_limit_full'      => __( 'Time Limit, Full', 'learndash-quiz-customizer' ),
				'time_limit_small_bar' => __( 'Time Limit, Small Bar', 'learndash-quiz-customizer' ),
				'time_limit_text_only' => __( 'Time Limit, Text Only', 'learndash-quiz-customizer' )
			),
			'section'     => 'lqc_quiz_sticky',
			'settings'    => 'lqc_quiz_options[stick_to_bottom]',
			'priority'    => 15
		) );

		/**
		 * Sticky: Max Width
		 */
		$wp_customize->add_setting( 'lqc_quiz_options[sticky_max_width]', array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field'
		) );
		$wp_customize->add_control( 'lqc_quiz_options[sticky_max_width]', array(
			'type'     => 'number',
			'label'    =>  __( 'Sticky: Max Width', 'learndash-quiz-customizer' ),
			'input_attrs' => array(
				'min'   => 1,
				'style' => 'width: 70px; display: block;'
			),
			'description' => __( 'Value is in pixels (px).', 'learndash-quiz-customizer' ),
			'section'  => 'lqc_quiz_sticky',
			'settings' => 'lqc_quiz_options[sticky_max_width]',
			'priority' => 20
		) );

	} // function lqc_quiz_sticky_section( $wp_customize )


	/**
	 * Sanitize Checkbox
	 *
	 * Accepts only "true" or "false" as possible values.
	 *
	 * @param $input
	 *
	 * @access public
	 * @since  1.0
	 * @return bool
	 */
	public function lqc_sanitize_checkbox( $input ) {
		return ( $input === true ) ? true : false;
	}

} // class LQC_Learndash_Quiz_Customizer_Setup