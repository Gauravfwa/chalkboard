<?php
/**
 * @TODO
 * - remove comments for old method of translation
 * 
 * Table of Contents
 *
 * 1.0 - Buttons
 *   1.1  - Start Quiz
 *   1.2  - Finish Quiz
 *   1.3  - Restart Quiz
 *   1.4  - Next
 *   1.5  - Back
 *   1.6  - Check
 *   1.7  - Hint (Button)
 *   1.8  - Skip Question
 *   1.9  - Review Question
 *   1.10 - Quiz Summary
 *   1.11 - View Questions
 *   1.12 - View Quiz Statistics
 *   1.13 - Show Leaderboard
 *   1.14 - Print Certificate
 *
 * 2.0 - Labels
 *   2.1  - X point(s)
 *   2.2  - Question X of Y
 *   2.3  - X. Question
 *   2.4  - Quiz Summary
 *   2.5  - Answered
 *   2.6  - Review
 *   2.7  - Sort Elements
 *   2.8  - Hint (heading label)
 *   2.9  - Correct
 *   2.10 - Incorrect
 *   2.11 - Points (NO OPTION)
 *   2.12 - Average Score
 *   2.13 - Your Score
 *   2.14 - Your Time: 00:00:00
 *   2.15 - Earned Points: X of Y
 *   2.16 - Time Limit (NO OPTION)
 *   2.17 - Essays Pending
 *   2.18 - Category: X
 *   2.19 - Not Categorized
 *   2.20 - Categories (heading)
 * 
 * 3.0 - Messages
 *   3.1  - X of Y Questions Answered Correctly (NO OPTION)
 *          (Removed because people were having issues with translation)
 *   3.2  - Quiz Locked
 *   3.3  - Quiz Registered Users Only
 *   3.4  - Quiz Prerequisite
 *   3.5  - Quiz Complete
 *   3.6  - Time Elapsed
 *   3.7  - Reached X of Y Points (NO OPTION)
 *   3.8  - Leaderboard: Intro Message
 *   3.9  - Essay: Upload Answer
 *   3.10 - Essay: Graded, Full Points Awarded
 *   3.11 - Essay: Not Graded, Full Points Awarded
 *   3.12 - Essay: Not Graded, No Points Awarded
 *   3.13 - Essay: Graded, Review
 *   3.14 - Essay: Textarea Placeholder
 *   3.15 - Certificate Pending
 */

/**
 * Override the various message shown via the LearnDash WPProQuiz output
 *
 * Available Variables:
 * $quiz_post_id : (integer) Current Quiz Post ID being display. 
 * $context : A unique label to distunquish the message and is used below to match the message to the optional replacement message.
 * $message : This is the message to be displayed. THIS MUST BE RETURNED
 * $placeholders : Array of placeholder values used in message. If used by $message. $placeholders[0] is first placeholder value, $placeholders[1] second etc. 
 * 
 * @since 2.4
 * 
 * @package LearnDash\Course
 */


/**
 * Create a variable for the serialized array.
 * All options are stored in this array.
 * Ex: $lqc_option[name_of_specific_option]
 * This just makes the code easier to read when retrieving options.
 */
$lqc_option = get_option( 'lqc_quiz_options' );


/**
 * Begin Switch Statement
 *
 * This contains ALL possible quiz customizations that LD makes available.
 *
 * @since 1.0
 */
switch( $context ) {

/**
 * 1.0 - Buttons
 */

	/**
	 * 1.1 - Start Quiz
	 */
	// Default Message: 'Start Quiz'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_start_button_label':
		if( isset( $lqc_option['btn_start_quiz'] ) && $lqc_option['btn_start_quiz'] != '' ) :

			$message = $lqc_option['btn_start_quiz'];

		else :

			// $message = __( 'Start', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'quiz' );

			$message = sprintf( esc_html_x( 'Start %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		endif;
	break;

	/**
	 * 1.2 - Finish Quiz
	 */
	// Default Message: 'Finish Quiz'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_finish_button_label':
		if( isset( $lqc_option['btn_finish_quiz'] ) && $lqc_option['btn_finish_quiz'] != '' ) :

			$message = $lqc_option['btn_finish_quiz'];

		else :

			// $message = __( 'Finish', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'quiz' );

			$message = sprintf( esc_html_x( 'Finish %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		endif;
	break;

	/**
	 * 1.3 - Restart Quiz
	 */
	// Default Message: 'Restart Quiz'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_restart_button_label':
		if( isset( $lqc_option['btn_restart_quiz'] ) && $lqc_option['btn_restart_quiz'] != '' ) :

			$message = $lqc_option['btn_restart_quiz'];

		else :

			// $message = __( 'Restart', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'quiz' );

			$message = sprintf( esc_html_x( 'Restart %s', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		endif;
	break;

	/**
	 * 1.4 - Next
	 */
	// Default Message: 'Next'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_next_button_label':
		if( isset( $lqc_option['btn_next_question'] ) && $lqc_option['btn_next_question'] != '' ) :

			$message = $lqc_option['btn_next_question'];

		else :

			$message = __( 'Next', 'learndash-quiz-customizer' );

		endif;
	break;

	/**
	 * 1.5 - Back
	 */
	// Default Message: 'Back'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_back_button_label':
		if( isset( $lqc_option['btn_back_question'] ) && $lqc_option['btn_back_question'] != '' ) :

			$message = $lqc_option['btn_back_question'];

		else :

			$message = __( 'Back', 'learndash-quiz-customizer' );

		endif;
	break;

	/**
	 * 1.6 - Check
	 */
	// Default Message: 'Check'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_check_button_label':
		if( isset($lqc_option['btn_check_question'] ) && $lqc_option['btn_check_question'] != '' ) :

			$message = $lqc_option['btn_check_question'];

		else :

			$message = __( 'Check', 'learndash-quiz-customizer' );

		endif;
	break;

	/**
	 * 1.7 - Hint (Button)
	 */
	// Default Message: 'Hint'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_hint_button_label':
		if( isset( $lqc_option['btn_hint_question'] ) && $lqc_option['btn_hint_question'] != '' ) :

			$message = $lqc_option['btn_hint_question'];

		else :

			$message = __( 'Hint', 'learndash-quiz-customizer' );

		endif;
	break;

	/**
	 * 1.8 - Skip Question
	 */
	// Default Message: 'Skip question'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_skip_button_label':
		if( isset( $lqc_option['btn_skip_question'] ) && $lqc_option['btn_skip_question'] != '' ) :

			$message = $lqc_option['btn_skip_question'];

		else :

			// $message = __( 'Skip', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'question' );

			$message = sprintf( esc_html_x( 'Skip %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) );

		endif;
	break;

	/**
	 * 1.9 - Review Question
	 */
	// Default Message: 'Review question'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_review_question_button_label':
		if( isset( $lqc_option['btn_review_question'] ) && $lqc_option['btn_review_question'] != '' ) :

			$message = $lqc_option['btn_review_question'];

		else :

			// $message = __( 'Review', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'question' );

			$message = sprintf( esc_html_x( 'Review %s', 'placeholder: Question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'question' ) );

		endif;
	break;

	/**
	 * 1.10 - Quiz Summary
	 */
	// Default Message: 'Quiz-summary'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_quiz_summary_button_label':
		if( isset( $lqc_option['btn_quiz_summary'] ) && $lqc_option['btn_quiz_summary'] != '' ) :

			$message = $lqc_option['btn_quiz_summary'];

		else :

			// $message = LearnDash_Custom_Label::get_label( 'quiz' ) . ' ' . __( 'Summary', 'learndash-quiz-customizer' );

			$message = sprintf( esc_html_x( '%s Summary', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		endif;
	break;

	/**
	 * 1.11 - View Questions
	 */
	// Default Message: 'View questions'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_view_questions_button_label':
		if( isset( $lqc_option['btn_view_questions'] ) && $lqc_option['btn_view_questions'] != '' ) :

			$message = $lqc_option['btn_view_questions'];

		else :

			// $message = __( 'View', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'questions' );

			$message = sprintf( esc_html_x( 'View %s', 'placeholder: Questions', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'questions' ) );

		endif;
	break;

	/**
	 * 1.12 - View Quiz Statistics
	 */
	// Default Message: 'View Quiz Statistics'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_view_statistics_button_label':
		if( isset( $lqc_option['btn_view_quiz_stats'] ) && $lqc_option['btn_view_quiz_stats'] != '' ) :

			$message = $lqc_option['btn_view_quiz_stats'];

		else :

			// $message = __( 'View', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::get_label( 'quiz' ) . ' ' . __( 'Statistics', 'learndash-quiz-customizer' );

			$message = sprintf( esc_html_x( 'View %s Statistics', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		endif;
	break;

	/**
	 * 1.13 - Show Leaderboard
	 */
	// Default Message: 'Show leaderboard'
	// Notes: This is a button label and should only contain simple text not HTML
	case 'quiz_show_leaderboard_button_label':
		if( isset( $lqc_option['btn_show_leaderboard'] ) && $lqc_option['btn_show_leaderboard'] != '' ) :
			$message = $lqc_option['btn_show_leaderboard'];
		else :
			$message = __( 'Show Leaderboard', 'learndash-quiz-customizer' );
		endif;
	break;

	/**
	 * 1.14 - Print Certificate
	 */
	// Default Message: 'PRINT YOUR CERTIFICATE'
	case 'quiz_certificate_button_label':

		if( isset( $lqc_option['btn_print_certificate'] ) && $lqc_option['btn_print_certificate'] != '' ) :

			$message = $lqc_option['btn_print_certificate'];

		else :

			$message = __( 'Print Your Certificate', 'learndash-quiz-customizer' );

		endif;

	break;


/**
 * 2.0 - Labels
 */

	/**
	 * 2.1 - X point(s)
	 */
	// Default Message: '<span>X</span> point(s)'
	// Notes: This message contains 1 number represented by X wrapped in span HTML
	case 'quiz_question_points_message':

		if( isset( $lqc_option['label_x_points'] ) && $lqc_option['label_x_points'] == 'two' ) :

			$message = '<div class="lqc-available-points">' . __( 'Points: ', 'learndash-quiz-customizer' ) . '<span class="lqc-number">' . $placeholders[0] . '</span></div>';

		elseif( isset( $lqc_option['label_x_points'] ) && $lqc_option['label_x_points'] == 'three' ) :

			$message = '<div class="lqc-available-points">' . __( 'Points = ', 'learndash-quiz-customizer' ) . '<span class="lqc-number">' . $placeholders[0] . '</span></div>';

		else :

			// Check for single vs. plural
			if( $placeholders[0] == '1' ) :

				$message = '<div class="lqc-available-points"><span class="lqc-number">' . $placeholders[0] . '</span> ' . __( 'Point', 'learndash-quiz-customizer' ) . '</div>';

			else :

				$message = '<div class="lqc-available-points"><span class="lqc-number">' . $placeholders[0] . '</span> ' . __( 'Points', 'learndash-quiz-customizer' ) . '</div>';

			endif; // single vs. plural

		endif;

	break;

	/**
	 * 2.2 - Question X of Y
	 */
	// Default Message: 'Question <span>X</span> of <span>Y</span>'
	// Notes: This message contains 2 numbers represented by X and Y wrapped in span HTML
	case 'quiz_question_list_2_message':
		
	if( isset( $lqc_option['label_question_list_2'] ) && $lqc_option['label_question_list_2'] == 'two' ) :

			$message = '<div class="lqc-question-list-2">' . LearnDash_Custom_Label::get_label( 'question' ) . ' <span class="lqc-number">' . $placeholders[0] . '</span> <span class="lqc-separator">/</span> <span class="lqc-number">' . $placeholders[1] . '</span></div>';

		elseif( isset( $lqc_option['label_question_list_2'] ) && $lqc_option['label_question_list_2'] == 'three' ) :

			$message = '<div class="lqc-question-list-2">' . LearnDash_Custom_Label::get_label( 'question' ) . ' <span class="lqc-number">' . $placeholders[0] . '</span><span class="lqc-separator">/</span><span class="lqc-number">' . $placeholders[1] . '</span></div>';

		elseif( isset( $lqc_option['label_question_list_2'] ) && $lqc_option['label_question_list_2'] == 'four' ) :

			$message = '<div class="lqc-question-list-2"><span class="lqc-number">' . $placeholders[0] . '</span> <span class="lqc-separator">' . __( 'of', 'learndash-quiz-customizer' ) . '</span> <span class="lqc-number">' . $placeholders[1] . '</span></div>';

		elseif( isset( $lqc_option['label_question_list_2'] ) && $lqc_option['label_question_list_2'] == 'five' ) :

			$message = '<div class="lqc-question-list-2"><span class="lqc-number">' . $placeholders[0] . '</span> <span class="lqc-separator">/</span> <span class="lqc-number">' . $placeholders[1] . '</span></div>';

		elseif( isset( $lqc_option['label_question_list_2'] ) && $lqc_option['label_question_list_2'] == 'six' ) :

			$message = '<div class="lqc-question-list-2"><span class="lqc-number">' . $placeholders[0] . '</span><span class="lqc-separator">/</span><span class="lqc-number">' . $placeholders[1] . '</span></div>';

		else :

			$message = '<div class="lqc-question-list-2">' . sprintf( esc_html_x( 'Question %1$s of %2$s', 'placeholder: question number, questions total', 'learndash' ), '<span>' . $placeholders[0] . '</span>', '<span>' . $placeholders[1] . '</span>' ) . '</div>';

		endif;

	break;

	/**
	 * 2.3 - X. Question
	 */
	// Default Message: '<span>X</span> Question'
	// Notes: This message contains 1 numbers represented by X and wrapped in span HTML
	case 'quiz_question_list_1_message':

		if( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'two' ) :

			$message = '<div class="lqc-question-list-1"><span class="lqc-number">' . $placeholders[0] . '</span>) ' . LearnDash_Custom_Label::get_label( 'question' ) . '</div>';

		elseif( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'three' ) :

			$message = '<div class="lqc-question-list-1"><span class="lqc-number">' . $placeholders[0] . '</span>: ' . LearnDash_Custom_Label::get_label( 'question' ) . '</div>';

		elseif( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'four' ) :

			$message = '<div class="lqc-question-list-1"><span class="lqc-number">' . $placeholders[0] . '</span> - ' . LearnDash_Custom_Label::get_label( 'question' ) . '</div>';

		elseif( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'five' ) :

			$message = '<div class="lqc-question-list-1">' . LearnDash_Custom_Label::get_label( 'question' ) . ' <span class="lqc-number">' . $placeholders[0] . '</span></div>';

		elseif( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'six' ) :

			$message = '<div class="lqc-question-list-1">' . LearnDash_Custom_Label::get_label( 'question' ) . ' #<span class="lqc-number">' . $placeholders[0] . '</span></div>';

		elseif( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'seven' ) :

			$message = '<div class="lqc-question-list-1">' . LearnDash_Custom_Label::get_label( 'question' ) . '</div>';

		elseif( isset( $lqc_option['label_question_list_1'] ) && $lqc_option['label_question_list_1'] == 'eight' ) :

			$message = '<div class="lqc-question-list-1"><span class="lqc-number lqc-number-only">' . $placeholders[0] . '</span></div>';

		else :

			$message = '<div class="lqc-question-list-1"><span class="lqc-number">' . $placeholders[0] . '</span>. ' . LearnDash_Custom_Label::get_label( 'question' ) . '</div>';

		endif;

	break;

	/**
	 * 2.4 - Quiz Summary
	 */
	// Default Message: 'Quiz-summary'
	// Notes: This header is wrapped in <h4></h4>
	case 'quiz_quiz_summary_header':

		if( isset( $lqc_option['label_quiz_summary'] ) && $lqc_option['label_quiz_summary'] != '' ) :

			$message = $lqc_option['label_quiz_summary'];

		else :

			// $message = LearnDash_Custom_Label::get_label( 'quiz' ) . __( ' Summary', 'learndash-quiz-customizer' );

			$message = sprintf( esc_html_x( '%s Summary', 'placeholder: Quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::get_label( 'quiz' ) );

		endif;

	break;

	// Default Message: '<p><span>0</span> of XXX questions completed</p>'
	// Notes: The <span>0</span> at the start of the message is required and will be populated JavaScript. The XXX
	// will be a number of the total questions from the quiz.
	case 'quiz_checkbox_questions_complete_message':

		$message = '<p>' . sprintf( esc_html_x( '%1$s of %2$s questions completed', 'placeholders: quiz count completed, quiz count total', 'learndash' ), '<span>0</span>', $placeholders[1] ) . '</p>';

	break;

	/**
	 * 2.5 - Answered
	 *
	 * Label used in the key when reviewing quiz questions.
	 */
	// Default Message: 'Answered'
	case 'quiz_quiz_answered_message':

		if( isset( $lqc_option['label_answered'] ) && $lqc_option['label_answered'] != '' ) :

			$message = $lqc_option['label_answered'];

		else :

			$message = __( 'Answered', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.6 - Review
	 *
	 * Label used in the key when reviewing quiz questions.
	 */
	// Default Message: 'Review'
	case 'quiz_quiz_review_message':

		if( isset( $lqc_option['label_review'] ) && $lqc_option['label_review'] != '' ) :

			$message = $lqc_option['label_review'];

		else :

			$message = __( 'Review', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.7 - Sort Elements
	 */
	// Default Message: 'Sort elements'
	case 'quiz_question_sort_elements_header':

		if( isset( $lqc_option['label_sort_elements'] ) && $lqc_option['label_sort_elements'] != '' ) :

			$message = $lqc_option['label_sort_elements'];

		else :

			$message = '';

		endif;

	break;

	/**
	 * 2.8 - Hint (heading label)
	 */
	// Default Message: 'Hint'
	// Notes: This header is wrapped in <h5></h5>
	case 'quiz_hint_header':

		if( isset( $lqc_option['label_hint'] ) && $lqc_option['label_hint'] != '' ) :

			$message = $lqc_option['label_hint'];

		else :

			$message = '';

		endif;

	break;

	/**
	 * 2.9 - Correct
	 */
	// Default Message: 'Correct'
	case 'quiz_question_answer_correct_message':

		if( isset( $lqc_option['label_correct'] ) && $lqc_option['label_correct'] != '' ) :

			$message = $lqc_option['label_correct'];

		else :

			$message = __( 'Correct', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.10 - Incorrect
	 */
	// Default Message: 'Incorrect'
	case 'quiz_question_answer_incorrect_message':

		if( isset( $lqc_option['label_incorrect'] ) && $lqc_option['label_incorrect'] != '' ) :

			$message = $lqc_option['label_incorrect'];

		else :

			$message = __( 'Incorrect', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.11 - Points
	 */
	// Default Message: 'Points'
	// SKIPPED
	// Can't figure out where it's being output.

	/**
	 * 2.12 - Average Score
	 */
	// Default Message: 'Average score'
	case 'quiz_average_score_message':

		if( isset( $lqc_option['label_average_score'] ) && $lqc_option['label_average_score'] != '' ) :

			$message = $lqc_option['label_average_score'];

		else :

			$message = __( 'Average Score', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.13 - Your Score
	 */
	// Default Message: 'Your score'
	case 'quiz_your_score_message':

		if( isset( $lqc_option['label_your_score'] ) && $lqc_option['label_your_score'] != '' ) :

			$message = $lqc_option['label_your_score'];

		else :

			$message = __( 'Your Score', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.14 - Your Time: 00:00:00
	 */
	// Default Message: "Your time: <span></span>"
	case 'quiz_your_time_message':

		if( isset( $lqc_option['label_your_time'] ) && $lqc_option['label_your_time'] != '' ) :

			$message = $lqc_option['label_your_time'] . ' <span class="lqc-time"></span>';

		else :

			$message = _x( 'Your Time:', 'time spent on quiz', 'learndash-quiz-customizer' ) . ' <span class="lqc-time"></span>';

		endif;

	break;

	/**
	 * 2.15 - Earned Points: X of Y
	 */
	// Default Message: 'Earned Point(s): <span>0</span> of <span>0</span>, (<span>0</span>)'
	// Notes: The <span>0</span> placeholders are required and populated via JavaScript when the quiz is completed. 
	case 'quiz_earned_points_message':

		if( isset( $lqc_option['label_earned_points'] ) && $lqc_option['label_earned_points'] == 'two' ) :

			$message = '<strong>' . __( 'Earned Points:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span> ' . __( 'of', 'learndash-quiz-customizer' ) . ' <span class="lqc-number"></span><span class="lqc-number" style="display:none;"></span>';

		elseif( isset( $lqc_option['label_earned_points'] ) && $lqc_option['label_earned_points'] == 'three' ) :

			$message = '<strong>' . __( 'Points Earned:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span> ' . __( 'of', 'learndash-quiz-customizer' ) . ' <span class="lqc-number"></span> (<span class="lqc-number"></span>)';

		elseif( isset( $lqc_option['label_earned_points'] ) && $lqc_option['label_earned_points'] == 'four' ) :

			$message = '<strong>' . __( 'Points Earned:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span> ' . __( 'of', 'learndash-quiz-customizer' ) . ' <span class="lqc-number"></span> (<span class="lqc-number" style="display:none;"></span>)';

		elseif( isset( $lqc_option['label_earned_points'] ) && $lqc_option['label_earned_points'] == 'five' ) :

			$message = '<strong>' . __( 'Score:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span> ' . __( 'of', 'learndash-quiz-customizer' ) . ' <span class="lqc-number"></span> (<span class="lqc-number"></span>)';
		
		elseif( isset( $lqc_option['label_earned_points'] ) && $lqc_option['label_earned_points'] == 'six' ) :

			$message = '<strong>' . __( 'Grade:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number" style="display:none;"></span><span class="lqc-number"  style="display:none;"></span><span class="lqc-number"></span>';

		else :

			$message = sprintf( esc_html_x( 'Earned Point(s): %1$s of %2$s, (%3$s)', 'placeholder: points earned, points total, points percentage', 'learndash' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' );

		endif;

	break;

	/**
	 * 2.16 - Time Limit
	 */
	// Default Message: 'Time limit'
	// SKIPPED
	// When this is customized, it removes the countdown timer.
	// I even tried adding $placeholders[0] but that didn't work.



	/**
	 * 2.17 - Essays Pending
	 */
	// Default Message: '<span>0</span> Essay(s) Pending (Possible Point(s): <span>0</span>)'
	// Notes: The <span>0</span> placeholders are required and populated via JavaScript when the quiz is completed. 
	case 'quiz_essay_possible_points_message':

		if( isset( $lqc_option['label_essays_pending'] ) && $lqc_option['label_essays_pending'] == 'two' ) :

			if( $placeholders[0] == '1' ) :

				$message = '<span class="lqc-number"></span> ' . __( 'Essay Pending', 'learndash-quiz-customizer' ) . ' (<span class="lqc-number"></span> ' . __( 'Possible Points', 'learndash-quiz-customizer' ) . ')';

			else :

				$message = '<span class="lqc-number"></span> ' . __( 'Essays Pending', 'learndash-quiz-customizer' ) . ' (<span class="lqc-number"></span> ' . __( 'Possible Points', 'learndash-quiz-customizer' ) . ')';

			endif;

		elseif( isset( $lqc_option['label_essays_pending'] ) && $lqc_option['label_essays_pending'] == 'three' ) :

			if( $placeholders[0] == '1' ) :

				$message = '<span class="lqc-number"></span> ' . __( 'Essay Pending', 'learndash-quiz-customizer' ) . '<span class="lqc-number" style="display:none;"></span>';

			else :

				$message = '<span class="lqc-number"></span> ' . __( 'Essays Pending', 'learndash-quiz-customizer' ) . '<span class="lqc-number" style="display:none;"></span>';

			endif;

		elseif( isset( $lqc_option['label_essays_pending'] ) && $lqc_option['label_essays_pending'] == 'four' ) :

			$message = '<strong>' . __( 'Essays Pending:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span>';

		elseif( isset( $lqc_option['label_essays_pending'] ) && $lqc_option['label_essays_pending'] == 'five' ) :

			$message = '<strong>' . __( 'Essays Pending:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span><br /><strong>' . __( 'Possible Points:', 'learndash-quiz-customizer' ) . '</strong> <span class="lqc-number"></span>';

		elseif( isset( $lqc_option['label_essays_pending'] ) && $lqc_option['label_essays_pending'] == 'six' ) :

			// $message = '<span class="lqc-number" style="display:none;"></span><span class="lqc-number"></span> ' . __( 'points awaiting a grade', 'learndash-quiz-customizer' ) . '.';

			$message = sprintf( esc_html_x( '%1$s points awaiting a grade.', 'placeholder: number of points', 'learndash-quiz-customizer' ), '<span class="lqc-number" style="display:none;"></span><span class="lqc-number"></span> ' );

		else :

			$message = sprintf( esc_html_x( '%1$s Essay(s) Pending (Possible Point(s): %2$s)', 'placeholder: number of essays, possible points ', 'learndash' ), '<span>0</span>', '<span>0</span>' );

		endif;

	break;

	/**
	 * 2.18 - Category: X
	 */
	// Default Message: 'Category: <span>S</span>'
	// Notes: This message contains 1 string represented by S wrapped in span HTML
	case 'quiz_question_category_message':

		if( isset( $lqc_option['label_category_x'] ) && $lqc_option['label_category_x'] != '' ) :

			$message = '<p class="lqc-category-label">' . $lqc_option['label_category_x'] . ' <span>' . $placeholders[0] . '</span></p>';

		else :

			$message = '<p class="lqc-category-label">' . __( 'Category:', 'learndash-quiz-customizer' ) . '<span> ' . $placeholders[0] . '</span></p>';

		endif;

	break;

	/**
	 * 2.19 - Not Categorized
	 */
	// Default Message: 'Not categorized'
	case 'learndash_not_categorized_message':

		if( isset( $lqc_option['label_not_categorized'] ) && $lqc_option['label_not_categorized'] != '' ) :

			$message = $lqc_option['label_not_categorized'];

		else :

			$message = __( 'Not Categorized', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 2.20 - Categories (heading)
	 */
	// Default Message: 'Categories'
	// Notes: This header is wrapped in <h4></h4>
	case 'learndash_categories_header':

		if( isset( $lqc_option['label_categories_header'] ) && $lqc_option['label_categories_header'] != '' ) :

			$message = $lqc_option['label_categories_header'];

		else :

			$message = __( 'Categories', 'learndash-quiz-customizer' );

		endif;

	break;


/**
 * 3.0 - Messages
 */

	/**
	 * 3.1 - X of Y Questions Answered Correctly
	 */
	// Default Message: "<p><span class="wpProQuiz_correct_answer">0</span> of <span>0</span> questions answered correctly</p>"
	// Notes: The <span>0</span> placeholders are required and populated via JavaScript when the quiz is completed.
	// .wpProQuiz_correct_answer class is NEEDED in order to output the correct value
	case 'quiz_questions_answered_correctly_message':

		$message = '<p>' . sprintf( esc_html_x( '%1$s of %2$s questions answered correctly', 'placeholder: correct answer, question count', 'learndash' ), '<span class="wpProQuiz_correct_answer">0</span>', '<span>' . $placeholders[1] . '</span>' ) . '</p>';

	break;

	/**
	 * 3.2 - Quiz Locked
	 */
	// Default Message: '<p>You have already completed the %s before. Hence you can not start it again.</p>'
	case 'quiz_locked_message':

		if( isset( $lqc_option['message_quiz_locked'] ) && $lqc_option['message_quiz_locked'] != '' ) :

			$message = '<p>' . $lqc_option['message_quiz_locked'] . '</p>';

		else :

			// $message = '<p>You have already completed this %s. You cannot start it again.</p>';
			
			$message = '<p>' . sprintf( esc_html_x( 'You have already completed this %s. You cannot start it again.', 'placeholder: quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ) . '</p>';

		endif;

	break;

	/**
	 * 3.3 - Quiz Registered Users Only
	 */
	// Default Message: '<p>You must sign in or sign up to start the quiz.</p>'
	case 'quiz_only_registered_user_message':

		if( isset( $lqc_option['message_registered_user_only'] ) && $lqc_option['message_registered_user_only'] != '' ) :

			$message = '<p>' . $lqc_option['message_registered_user_only'] . '</p>';

		else :

			$message = '<p>' . sprintf( esc_html_x( 'You must sign in or sign up to take this %s.', 'placeholder: quiz', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ) . '</p>';

		endif;

	break;

	/**
	 * 3.4 - Quiz Prerequisite
	 */
	// Default Message: '<p>You must first complete the following: <span></span></p>'
	// Notes: The <span></span> at the end of the message is required and will be populated JavaScript with the prerequisite quizzes. 
	case 'quiz_prerequisite_message':

		if( isset( $lqc_option['message_quiz_prerequisite'] ) && $lqc_option['message_quiz_prerequisite'] != '' ) :

			$message = '<p>' . $lqc_option['message_quiz_prerequisite'] . '<br /><span></span></p>';

		else :

			$message = '<p>' . _x( 'You must first complete the following:', 'a list of required quizzes is automatically output', 'learndash-quiz-customizer' ) . '<br /><span></span></p>';

		endif;

	break;

	/**
	 * 3.5 - Quiz Complete
	 */
	// Default Message: "Quiz complete. Results are being recorded."
	case 'quiz_complete_message':

		if( isset( $lqc_option['message_quiz_complete'] ) && $lqc_option['message_quiz_complete'] != '' ) :

			$message = '<p class="lqc-message lqc-quiz-complete">' . $lqc_option['message_quiz_complete'] . '<br /><span></span></p>';

		else :

			// $message = '<p class="lqc-message lqc-quiz-complete">' . LearnDash_Custom_Label::get_label( 'quiz' ) . ' ' . __( 'complete. Results are being recorded.', 'learndash-quiz-customizer' ) . '</p>';

			$message = '<p class="lqc-message lqc-quiz-complete">' . sprintf( esc_html_x( '%s complete. Results are being recorded.', 'placeholder: Quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ) . '</p>';

		endif;

	break;
	
	/**
	 * 3.6 - Time Elapsed
	 */
	// Default Message: "Time has elapsed"
	// It's wrapped in <p> tags. DON'T add any HTML to this one.
	case 'quiz_time_has_elapsed_message':

		if( isset( $lqc_option['message_time_elapsed'] ) && $lqc_option['message_time_elapsed'] != '' ) :

			$message = $lqc_option['message_time_elapsed'];

		else :

			$message = __( 'Time has elapsed.', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 3.7 - Reached X of Y Points
	 */
	// Default Message: 'You have reached <span>0</span> of <span>0</span> point(s), (<span>0</span>)'	
	// Notes: The <span>0</span> placeholders are required and populated via JavaScript when the quiz is completed. 
	case 'quiz_have_reached_points_message':

		$message = sprintf( esc_html_x( 'You have reached %1$s of %2$s point(s), (%3$s)', 'placeholder: points earned, points total', 'learndash' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' );

	break;

	/**
	 * 3.8 - Leaderboard: Intro Message
	 */
	// Default Message: '<span style="font-weight: bold;">Your result has been entered into leaderboard</span>'
	case 'quiz_toplist_results_message':

		if( isset( $lqc_option['message_leaderboard_intro'] ) && $lqc_option['message_leaderboard_intro'] != '' ) :

			$message = '<p>' . $lqc_option['message_leaderboard_intro'] . '</p>';

		else :

			$message = '<p>' . __( 'Would you like to add your score to the leaderboard?', 'learndash-quiz-customizer' ) . '</p>';

		endif;

	break;

	/**
	 * 3.9 - Essay: Upload Answer
	 */
	// Default Message: '<p>Upload your answer to this question.</p>'
	case 'quiz_essay_question_upload_answer_message':

		if( isset( $lqc_option['message_essay_upload_answer'] ) && $lqc_option['message_essay_upload_answer'] != '' ) :

			$message = '<p class="lqc-message lqc-upload-your-answer">' . $lqc_option['message_essay_upload_answer'] . '</p>';

		else :

			// $message = '<p class="lqc-message lqc-upload-your-answer">' . __( 'Upload your answer to this', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::label_to_lower( 'question' ) . '.</p>';

			$message = '<p class="lqc-message lqc-upload-your-answer">' . sprintf( esc_html_x( 'Upload your answer to this %s.', 'placeholder: question', 'learndash-quiz-customizer' ), LearnDash_Custom_Label::label_to_lower( 'question' ) ) . '</p>';

		endif;

	break;	
	
	/**
	 * 3.10 - Essay: Graded, Full Points Awarded
	 */
	// Default Message: 'This response will be awarded full points automatically, but it can be reviewed and adjusted after submission.'	
	case 'quiz_essay_question_graded_full_message':

		if( isset( $lqc_option['message_essay_graded_full'] ) && $lqc_option['message_essay_graded_full'] != '' ) :

			$message = $lqc_option['message_essay_graded_full'];

		else :

			$message = __( 'Your response will be awarded full points automatically, but it can be reviewed and adjusted after submission.', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 3.11 - Essay: Not Graded, Full Points Awarded
	 */
	// Default Message: 'This response will be awarded full points automatically, but it will be reviewed and possibly adjusted after submission.'
	case 'quiz_essay_question_not_graded_full_message':

		if( isset( $lqc_option['message_essay_notgraded_full'] ) && $lqc_option['message_essay_notgraded_full'] != '' ) :

			$message = $lqc_option['message_essay_notgraded_full'];

		else :

			$message = __( 'Your response will be awarded full points automatically, but it will be reviewed and possibly adjusted after submission.', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 3.12 - Essay: Not Graded, No Points Awarded
	 */
	// Default Message: 'This response will be reviewed and graded after submission.'	
	case 'quiz_essay_question_not_graded_none_message':	

		if( isset( $lqc_option['message_essay_notgraded_none'] ) && $lqc_option['message_essay_notgraded_none'] != '' ) :

			$message = $lqc_option['message_essay_notgraded_none'];

		else :

			$message = __( 'Your response will be reviewed and graded after submission.', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 3.13 - Essay: Graded, Review
	 */
	// Default Message: 'Grading can be reviewed and adjusted.'	
	case 'quiz_essay_question_graded_review_message':

		if( isset( $lqc_option['message_essay_graded_review'] ) && $lqc_option['message_essay_graded_review'] != '' ) :

			$message = $lqc_option['message_essay_graded_review'];

		else :

			$message = __( 'Your grade may be reviewed and adjusted.', 'learndash-quiz-customizer' );

		endif;

	break;

	/**
	 * 3.14 - Essay: Textarea Placeholder
	 */
	// Default Message: 'Type your response here'
	// Notes: This is shown on the Essay textarea as placeholder tesxt
	case 'quiz_essay_question_textarea_placeholder_message':

		if( isset( $lqc_option['message_essay_textarea_placeholder'] ) && $lqc_option['message_essay_textarea_placeholder'] != '' ) :

			$message = $lqc_option['message_essay_textarea_placeholder'];

		else :

			$message = __( 'Type your response here.', 'learndash-quiz-customizer' );

		endif;

	break;		

	/**
	 * 3.15 - Certificate Pending
	 */
	// Default Message: 'Certificate Pending - Questions still need to be graded, please check your profile for the status.'
	case 'quiz_certificate_pending_message':

		if( isset( $lqc_option['message_certificate_pending'] ) && $lqc_option['message_certificate_pending'] != '' ) :

			$message = $lqc_option['message_certificate_pending'];

		else :

			// $message = '<strong>' . __( 'Certificate Pending:', 'learndash-quiz-customizer' ) . '</strong> ' . __( 'Some', 'learndash-quiz-customizer' ) . ' ' . LearnDash_Custom_Label::label_to_lower( 'questions' ) . ' ' . __( 'still need to be graded.', 'learndash-quiz-customizer' );

			$message = sprintf( esc_html_x( '%1$sCertificate Pending:%2$s Some %3$s still need to be graded.', 'placeholders: <strong>, </strong>, "questions"', 'learndash-quiz-customizer' ), '<strong>', '</strong>', LearnDash_Custom_Label::label_to_lower( 'questions' ) );

		endif;

	break;

	// Not match on 'context'. 	
	default:
	break;
}

// Finally echo $message
echo $message;
