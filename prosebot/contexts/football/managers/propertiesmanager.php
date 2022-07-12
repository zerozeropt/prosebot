<?php

require_once(__DIR__.'/../../propertiesmanager.php');
require_once(__DIR__.'/../../../utils.php');

/**
 * Class to manage conditions variables for Football context
 * 
 * @author zerozero.pt
 */
class PropertiesManagerFootball extends PropertiesManager
{
	/**
	 * Set properties for conditions
     */
	public static function construct_properties()
	{
		// List of properties
		static::$template_properties = array(
			new Property('player_goal', function ($match, $n) {
				return strval($match->get_events()[$n]->get_scorer_n());
			}, 10),
			new Property('fixture', function ($match, $n) {
				return intval($match->get_fixture());
			}, 10),
			new Property('player_goals', function ($match, $n) {
				return strval($match->get_events()[$n]->get_score_player()->get_goals());
			}, 1),
			new Property('has_turn_around', function ($match, $n) {
				return Utils::boolstr($match->has_turnaround());
			}, 100),
			new Property('has_assist', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->get_assist_player() !== null);
			}, 15),
			new Property('zone_goal', function ($match, $n) {
				return Utils::boolstr(1);
			}, 2),
			new Property('is_inside_box_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->inside_box_goal());
			}, 1),
			new Property('is_outside_box_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->outside_box_goal());
			}, 5),
			new Property('penalty_defend', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->get_goalkeeper() !== null);
			}, 1),
			new Property('is_own_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->get_own_goal());
			}, 100),
			new Property('goal_type', function ($match, $n) {
				return Utils::boolstr(1);
			}, 2),
			new Property('is_left_foot_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->left_foot_goal());
			}, 1),
			new Property('is_right_foot_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->right_foot_goal());
			}, 1),
			new Property('is_head_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->head_goal());
			}, 3),
			new Property('is_direct_freekick_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->direct_free_kick_goal());
			}, 15),
			new Property('is_indirect_freekick_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->indirect_free_kick_goal());
			}, 10),
			new Property('is_corner_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->corner_kick_goal());
			}, 15),
			new Property('is_running_ball_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->running_ball_goal());
			}, 5),
			new Property('is_direct_corner_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->direct_corner_kick_goal());
			}, 50),
			new Property('is_throw_in_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->throw_in_goal());
			}, 50),
			new Property('is_penalty_rebound_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->penalty_rebound_goal());
			}, 50),
			new Property('is_penalty_goal', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->penalty_goal());
			}, 100),
			new Property('is_coach', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->coach());
			}, 1),
			new Property('team_goal', function ($match, $n) {
				return strval($match->get_events()[$n]->get_team_n());
			}, 8),
			new Property('team_goals', function ($match, $n) {
				return strval($match->get_events()[$n]->get_team()->get_goals());
			}, 1),
			new Property('match_goal', function ($match, $n) {
				return strval($match->get_events()[$n]->get_match_n());
			}, 9),
			new Property('home_team_goal', function ($match, $n) {
				return Utils::boolstr($match->get_home_goals() != 0);
			}, 1),
			new Property('starters', function ($match, $n) {
				return Utils::boolstr(count($match->get_starters()) != 0);
			}, 1),
			new Property('benched', function ($match, $n) {
				return Utils::boolstr(count($match->get_benched()) != 0);
			}, 1),
			new Property('away_team_goal', function ($match, $n) {
				return Utils::boolstr($match->get_away_goals() != 0);
			}, 1),
			new Property('match_goals', function ($match, $n) {
				return strval($match->goals());
			}, 1),
			new Property('first_competition_game', function ($match, $n) {
				return $match->get_first_competition_game();
			}, 1),
			new Property('player_assist', function ($match, $n) {
				return strval($match->get_events()[$n]->get_assist_n());
			}, 15),
			new Property('final_score_diff', function ($match, $n) {
				return strval(abs($match->score_diff()));
			}, 10),
			new Property('prev_match_final_score_diff', function ($match, $n) {
				return strval(abs($match->prev_match_score_diff()));
			}, 10),
			new Property('winner', function ($match, $n) {
				return $match->winner();
			}, 2),
			new Property('loser', function ($match, $n) {
				return $match->loser();
			}, 2),
			new Property('home_team', function ($match, $n) {
				return $match->get_home_team_id();
			}, 2),
			new Property('away_team', function ($match, $n) {
				return $match->get_away_team_id();
			}, 2),
			new Property('minute', function ($match, $n) {
				return strval($match->get_events()[$n]->get_minute()[0]);
			}, 2),
			new Property('minute_ratio', function($match, $n){
				$minute = $match->get_events()[$n]->get_minute()[0];
				$duration = $match->get_duration();
				return strval($minute / $duration);
			}, 5),
			new Property('minute_extra', function ($match, $n) {
				return strval($match->get_events()[$n]->get_minute()[1]);
			}, 3),
			new Property('team_goals_diff', function ($match, $n) {
				return strval($match->get_events()[$n]->team_goals_diff());
			}, 2),
			new Property('total_goals', function ($match, $n) {
				return strval($match->get_events()[$n]->total_goals());
			}, 1),
			new Property('has_best_player', function ($match, $n) {
				return Utils::boolstr($match->get_best_player() !== null);
			}, 1),
			new Property('has_decisive_player', function ($match, $n) {
				return Utils::boolstr($match->get_decisive_player() !== null);
			}, 1),
			new Property('is_second_yellow', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->get_second_yellow());
			}, 1),
			new Property('home_win',  function ($match, $n) {
				return Utils::boolstr($match->home_team() == $match->winner() && !$match->is_neutral_ground());
			}, 1),
			new Property('away_win',  function ($match, $n) {
				return Utils::boolstr($match->away_team() == $match->winner() && !$match->is_neutral_ground());
			}, 1),
			new Property('is_gamechanger', function ($match, $n) {
				return Utils::boolstr($match->get_events()[$n]->has_positive_impact() && $match->improved_upon_event($n));
			}, 1),
			new Property('has_suprise', function ($match, $n) {
				return Utils::boolstr(($match->winner() != $match->home_team()) && (($match->loser()->get_pre_classification() - $match->winner()->get_pre_classification()) < -3));
			}, 100),
			new Property('stage', function ($match, $n) {
				return $match->get_stage();
			}, 1),
			new Property('is_league', function ($match, $n) {
				return Utils::boolstr($match->get_fixture() != 0);
			}, 1),
			new Property('is_final', function ($match, $n) {
				return Utils::boolstr($match->get_stage() == "F" && $match->get_competition_hand() == $match->get_competition_num_hands());
			}, 1),
			new Property('is_semi_final', function ($match, $n) {
				return Utils::boolstr($match->get_stage() == "MF" || $match->get_stage() == "SF" || $match->get_stage() == "1/2");
			}, 1),
			new Property('is_quarter_final', function ($match, $n) {
				return Utils::boolstr($match->get_stage() == "QF" || $match->get_stage() == "1/4");
			}, 1),
			new Property('is_round_of_16', function ($match, $n) {
				return Utils::boolstr($match->get_stage() == "1/8");
			}, 1),
			new Property('is_group_stage', function ($match, $n) {
				return Utils::boolstr($match->get_stage()[0] == 'G');
			}, 1),
			new Property('next_game_same_teams', function ($match, $n) {
				$match_id_home = $match->home_team()->get_next_competition_match_id();
				$match_id_away = $match->away_team()->get_next_competition_match_id();
				return Utils::boolstr($match_id_home == $match_id_away);
			}, 1),
			new Property('is_next_match_day', function ($match, $n) {
				return Utils::boolstr($match->home_team()->is_next_match_day() && $match->away_team()->is_next_match_day());
			}, 1),
			new Property('is_last_match_day', function ($match, $n) {
				return Utils::boolstr($match->home_team()->is_last_match_day() && $match->away_team()->is_last_match_day());
			}, 1),
			new Property('has_stat_team', function ($match, $n) {
				return Utils::boolstr($match->get_stats()[$n]->get_team() !== null);
			}, 1),
			new Property('is_stat_positive', function ($match, $n) {
				return Utils::boolstr($match->get_stats()[$n]->is_positive());
			}, 1),
			new Property('is_stat_global', function ($match, $n) {
				return Utils::boolstr($match->get_stats()[$n]->is_global());
			}, 1),
			new Property('is_relevant_player', function ($match, $n) {
				return strval($match->get_events()[$n]->is_relevant_player());
			}, 1),
			new Property('has_previous_match', function ($match, $n) {
				return Utils::boolstr($match->has_previous_match());
			}, 1),
			new Property('has_match_stat', function ($match, $n) {
				return Utils::boolstr($match->current_match_stat !== false);
			}, 1),
			new Property('has_home_stat', function ($match, $n) {
				return Utils::boolstr($match->home_team()->get_current_stat() !== false);
			}, 1),
			new Property('has_away_stat', function ($match, $n) {
				return Utils::boolstr($match->away_team()->get_current_stat() !== false);
			}, 1),
			new Property('has_winner_stat', function ($match, $n) {
				return Utils::boolstr($match->winner()->get_current_stat() !== false);
			}, 1),
			new Property('has_loser_stat', function ($match, $n) {
				return Utils::boolstr($match->loser()->get_current_stat() !== false);
			}, 1),
			new Property('has_home_team_next_match', function ($match, $n) {
				return Utils::boolstr($match->home_team()->get_next_competition_match_id() !== null);
			}, 1),
			new Property('has_away_team_next_match', function ($match, $n) {
				return Utils::boolstr($match->away_team()->get_next_competition_match_id() !== null);
			}, 1),
			new Property('has_next_competition_game', function ($match, $n) {
				return Utils::boolstr($match->away_team()->get_next_competition_match_id() !== null || $match->home_team()->get_next_competition_match_id() !== null);
			}, 1),
			new Property('last_goal_stoppage_time', function ($match, $n) {
				$reversed = array_reverse($match->get_events());
				$last_goal = Utils::find($reversed, function ($elem) {
					return $elem::NAME === GoalEvent::NAME;
				});
	
				if ($last_goal === false) {
					return "0";
				}
	
				$minute = $reversed[$last_goal]->get_minute()[0];
				$minute_extra = $reversed[$last_goal]->get_minute()[1];
	
				return Utils::boolstr($minute == 90 && $minute_extra > 0);
			}, 100),
			new Property('decisive_goal_stoppage_time', function ($match, $n) {
				$reversed = array_reverse($match->get_events());
				$last_goal = Utils::find($reversed, function ($elem) {
					return $elem::NAME === GoalEvent::NAME;
				});
	
				if ($last_goal === false) {
					return "0";
				}
	
				$goal = $reversed[$last_goal];
				$minute = $reversed[$last_goal]->get_minute()[0];
				$minute_extra = $reversed[$last_goal]->get_minute()[1];
				$diff = abs($match->score_diff());
				$team = $goal->get_team()->get_id();
				$winner = $match->winner()->get_id();
	
				return Utils::boolstr(($minute == 90 && $minute_extra > 0) && ($diff === 0 || ($team == $winner && $diff === 1)));
			}, 150),
			new Property('dominance', function ($match, $n) {
				$winner = $match->winner();
				$loser = $match->loser();
	
				$winner_stats = $winner->get_match_stats();
				$loser_stats = $loser->get_match_stats();
	
				if(!(array_key_exists("shot", $winner_stats) && array_key_exists("ballpo", $winner_stats) && array_key_exists("shot", $loser_stats))){
					return "0";
				}
	
				$winner_shots = $winner_stats['shot'];
				$winner_ballpo = $winner_stats['ballpo'];
				$loser_shots = $loser_stats['shot'];
	
				return Utils::boolstr($winner_ballpo > 65 && (1 - $loser_shots / $winner_shots) > 0.3);
			}, 10),
			new Property('is_senior', function ($match) {
				return Utils::boolstr($match->get_senior());
			}, 1),
			new Property('is_mens', function ($match) {
				return Utils::boolstr($match->get_gender());
			}, 1),
			new Property('is_elim_game', function ($match) {
				return Utils::boolstr($match->get_competition_hand() == $match->get_competition_num_hands() && $match->get_competition_type() != "elim_po");
			}, 10),
			new Property('has_two_hands', function ($match) {
				return Utils::boolstr($match->get_competition_num_hands() == 2);
			}, 10),
			new Property('pre_curiosity_val', function ($match, $n) {
				return $match->get_curiosities()[$n]->get_pre_value();
			}, 1),
			new Property('post_curiosity_val', function ($match, $n) {
				return $match->get_curiosities()[$n]->get_post_value();
			}, 1),
			new Property('same_competition_form_sequence', function ($match) {
				$home_team = $match->home_team();
				$away_team = $match->away_team();
	
				$same_sequence_number = $home_team->get_form_number_competition() > 0 && $home_team->get_form_number_competition() === $away_team->get_form_number_competition();
				$same_sequence = $same_sequence_number && $home_team->get_form_competition()[0] === $away_team->get_form_competition()[0];
	
				return Utils::boolstr($same_sequence);
			}, 100),
			new Property("has_classification", function ($match) {
				return Utils::boolstr($match->home_team()->get_pos_classification() != null && $match->away_team()->get_pos_classification() != null);
			}, 1),
			new Property('extra_time', function ($match) {
				return $match->get_extra_time();
			}, 15),
			new Property('crowd', function ($match) {
				return $match->get_crowd();
			}, 1),
			new Property('crowd_ratio', function ($match) {
				return $match->get_crowd_ratio();
			}, 2),
			new Property('neutral_ground', function ($match) {
				return Utils::boolstr($match->is_neutral_ground());
			}, 1),
			new Property('loser_moves_on', function ($match) {
				return Utils::boolstr($match->loser_moves_on());
			}, 100),
			new Property('winner_moves_on', function ($match) {
				return Utils::boolstr($match->winner_moves_on());
			}, 100),
		);
		usort(static::$template_properties, function ($a, $b) {
			return strlen($b->name) - strlen($a->name);
		});

		// List of #arg.properties
		static::$template_arg_properties = array(
			new Property('#arg.is_away_team', function ($match, $n, $arg) {
				return Utils::boolstr($arg == $match->away_team());
			}),
			new Property('#arg.is_global_winning_sequence', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_global_winning_sequence());
			}),
			new Property('#arg.is_global_drawing_sequence', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_global_drawing_sequence());
			}),
			new Property('#arg.is_global_losing_sequence', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_global_losing_sequence());
			}),
			new Property('#arg.global_form_sequence', function ($match, $n, $arg) {
				return $arg->get_entity(null, "global_form_sequence");
			}),
			new Property('#arg.is_coach', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_coach());
			}),
			new Property('#arg.is_competition_winning_sequence', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_competition_winning_sequence());
			}),
			new Property('#arg.is_competition_drawing_sequence', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_competition_drawing_sequence());
			}),
			new Property('#arg.is_competition_losing_sequence', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_competition_losing_sequence());
			}),
			new Property('#arg.competition_form_sequence', function ($match, $n, $arg) {
				return $arg->get_entity(null, "competition_form_sequence");
			}),
			new Property('#arg.competition_form_sequence_bool', function ($match, $n, $arg) {
				return Utils::boolstr($arg->get_entity(null, "competition_form_sequence"));
			}),
			new Property('#arg.is_next_global_home', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_next_global_home());
			}),
			new Property('#arg.is_next_global_away', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_next_global_away());
			}),
			new Property('#arg.is_next_competition_home', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_next_competition_home());
			}),
			new Property('#arg.is_next_competition_away', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_next_competition_away());
			}),
			new Property('#arg.is_next_competition_neutral', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_next_competition_neutral());
			}),
			new Property('#arg.has_next_competition_game', function ($match, $n, $arg) {
				return Utils::boolstr($arg->get_next_competition_match_id() != null);
			}),
			new Property('#arg.is_first_mention', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_first_mention($n));
			}),
			new Property('#arg.is_positive', function ($match, $n, $arg) {
				return Utils::boolstr($arg->is_positive());
			}),
			new Property('#arg.key', function ($match, $n, $arg) {
				return $arg->get_key();
			}),
			new Property('#arg.has_red_card', function ($match, $n, $arg) {
				return Utils::boolstr($arg->has_red_card());
			}),
			new Property('#arg.minute_in', function ($match, $n, $arg) {
				return strval($arg->get_minute_in());
			}),
			new Property('#arg.report_num_goals', function ($match, $n, $arg) {
				$season_goals = $arg->get_entity(null, "season_goals", PHP_INT_MAX, $n, $match->get_events()[$n]);
				$goal = $match->get_events()[$n]->get_scorer_n();
				$goals = $arg->get_goals();
				return Utils::boolstr(($season_goals > 0 && $season_goals % 5 === 0 && $goals < 3) || $goals >= 3 && $goal == $goals);
			}),
			new Property('#arg.is_stat_ball_po', function ($match, $n, $arg) {
				return Utils::boolstr($arg->stat_type == "ballpo");
			}),
			new Property('#arg.is_stat_shots', function ($match, $n, $arg) {
				return Utils::boolstr($arg->stat_type == "shots");
			}),
			new Property('#arg.player_goals', function ($match, $n, $arg) {
				return $arg->get_goals();
			}),
			new Property('#arg.player_assists', function ($match, $n, $arg) {
				return $arg->get_assists();
			}),
			new Property('#arg.player_goal', function ($match, $n, $arg) {
				return $match->get_events()[$n]->get_scorer_n();
			}),
			new Property('#arg.consecutive_matches_scoring', function ($match, $n, $arg) {
				return $arg->get_entity(null, "consecutive_matches_scoring");
			}),
			new Property('#arg.winningless_streak', function ($match, $n, $arg) {
				return $arg->get_curiosity("winningless_streak")->get_value();
			}),
			new Property('#arg.lossless_streak', function ($match, $n, $arg) {
				return $arg->get_curiosity("lossless_streak")->get_value();
			}),
			new Property('#arg.no_goals_conceded_streak', function ($match, $n, $arg) {
				return $arg->get_curiosity("no_goals_conceded_streak")->get_value();
			}),
			new Property('#arg.goals_scored_streak', function ($match, $n, $arg) {
				return $arg->get_curiosity("goals_scored_streak")->get_value();
			}),
			new Property('#arg.first_loss', function ($match, $n, $arg) {
				$curiosity = $arg->get_curiosity("losses");
				return $curiosity->get_pre_value() == 0 && $curiosity->get_post_value() > 0;
			}),
			new Property('#arg.first_non_win', function ($match, $n, $arg) {
				$curiosity = $arg->get_curiosity("games_without_winning");
				return $curiosity->get_pre_value() == 0 && $curiosity->get_post_value() > 0;
			}),
			new Property('#arg.pre_classification', function ($match, $n, $arg) {
				return $arg->get_pre_classification();
			}),
			new Property('#arg.pos_classification', function ($match, $n, $arg) {
				return $arg->get_pos_classification();
			}),
		);
		usort(static::$template_arg_properties, function ($a, $b) {
			return strlen($b->name) - strlen($a->name);
		});
	}
}
?>