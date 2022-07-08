<?php

require_once(__DIR__.'/../../data_fetcher.php');

/**
 * Class for fetching Football data
 *
 * @author zerozero.pt
 */
class FootballFetcher extends DataFetcher
{
	/**
	 * API endpoints
	 */
	const SAMPLES_DIR = __DIR__.'/../samples';
	const MATCH_DATA_PATH = self::SAMPLES_DIR.'/matches/{match_id}.json';
	const H2H_DATA_PATH = self::SAMPLES_DIR.'/h2h/{match_id}.json';
	const TEAM_DATA_PATH = self::SAMPLES_DIR.'/teams/{team_id}.json';
	/**
	 * Entities hyperlinks
	 */
	const match_link = "<a href=https://www.zerozero.pt/match.php?id=";
	const team_link = "<a href=https://www.zerozero.pt/equipa.php?id=";
	const player_link = "<a href=https://www.zerozero.pt/player.php?id=";
	const coach_link = "<a href=https://www.zerozero.pt/coach.php?id=";
	const edition_link = "<a href=https://www.zerozero.pt/edicao.php?id_edicao=";

	/**
	 * Fetch match entity data
	 * @param string $match_id Id of the match
	 * @return JSON Decoded json with match data
	 */
	public static function get_match_json($match_id)
	{
		$api = preg_replace('/{match_id}/', $match_id, self::MATCH_DATA_PATH);
		$json = static::get_json($api);

		if($json == null) {
			throw new Exception("Error: Match " . $match_id . " does not exist.");
		}
		
		return $json;
	}

	/**
	 * Fetch match head to head data
	 * @param string $match_id Id of the match
	 * @return JSON Decoded json with match head to head data
	 */
	public static function get_h2h_json($match_id)
	{
		$api = preg_replace('/{match_id}/', $match_id, self::H2H_DATA_PATH);
		$json = static::get_json($api);

		if($json == null) {
			throw new Exception("Error: Match " . $match_id . " does not exist.");
		}
		
		return $json;
	}

	/**
	 * Fetch team entity data
	 * @param string $team_id Id of the team
	 * @return JSON Decoded json with team data
	 */
	public static function get_team_json($team_id)
	{
		$api = preg_replace('/{team_id}/', $team_id, self::TEAM_DATA_PATH);
		$json = static::get_json($api);

		if ($json == null) {
			throw new Exception("Error: Team " . $team_id . " does not exist.");
		}

		return $json;
	}
}
?>