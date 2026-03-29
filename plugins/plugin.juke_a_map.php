<?php


// Start the plugin
$_PLUGIN = new PluginJukeAMap();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginJukeAMap extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('scr');
		$this->setVersion('1.0.0');
		// $this->setBuild('2017-04-27');
		// $this->setCopyright('2014 - 2017 by undef.de');

		$this->registerEvent('onSync',						'onSync');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onPlayerConnect',				'onPlayerConnect');
		$this->registerEvent('onBeginMap',					'onBeginMap');
		$this->registerEvent('onEndMapPrefix',				'onEndMap');
		// $this->registerEvent('onRestartMap',				'onRestartMap');

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {
		
		$this->config['MANIALINK_ID'][0] = 'JukeAMap2018';
		$this->config['BUTTON'][0]['POSITION_X'][0] = 61;
		$this->config['BUTTON'][0]['POSITION_Y'][0] = 68;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

	// Display the Button to new player
	$this->buildbutton($player->login);
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	public function onBeginMap ($aseco) {
	
	// Display the Button to all Players)
	$this->buildbutton(false);
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	public function onEndMap ($aseco, $race) {

	$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
	$xml .= '<manialinks>';
	$xml .= '<manialink id="'. $this->config['MANIALINK_ID'][0] .'01" version="3"></manialink>';
	$xml .= '<manialink id="'. $this->config['MANIALINK_ID'][0] .'02" version="3"></manialink>';
	$xml .= '</manialinks>';

	// Hide at Scoretable
	$aseco->client->query('SendDisplayManialinkPage', $xml, 0, false);
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
/*	public function onRestartMap ($aseco, $map) {

	// send empty Manialink
	$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
	$xml .= '<manialinks>';
	$xml .= '<manialink id="'. $this->bct_config['MANIALINK_ID'][0] .'01" version="3"></manialink>';
	$xml .= '<manialink id="'. $this->bct_config['MANIALINK_ID'][0] .'02" version="3"></manialink>';
	$xml .= '</manialinks>';
	
	$aseco->client->query('SendDisplayManialinkPage', $xml, 0, false);
   }
	
*/	
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {

	
	
	
	// Get Player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		if ($params['Action'] == 'Pressed') {
			$this->mapentries($login);
		}
		else {
			$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<manialinks>';
			$xml .= '<manialink id="'. $this->config['MANIALINK_ID'][0] .'02" version="3"></manialink>';
			$xml .= '</manialinks>';

			if ($params['Action'] == 'all') {
			$aseco->releaseChatCommand('/elist', $player->login);
			}
			if ($params['Action'] == 'newest') {
			$aseco->releaseChatCommand('/elist newest', $player->login);
			}
			if ($params['Action'] == 'worst') {
			$aseco->releaseChatCommand('/elist worst', $player->login);
			}
			if ($params['Action'] == 'nofinish') {
			$aseco->releaseChatCommand('/elist nofinish', $player->login);
			}
			if ($params['Action'] == 'norecent') {
			$aseco->releaseChatCommand('/elist norecent', $player->login);
			}
			if ($params['Action'] == 'norank') {
			$aseco->releaseChatCommand('/elist norank', $player->login);
			}
			if ($params['Action'] == 'showjukebox') {
			$aseco->releaseChatCommand('/jukebox display', $player->login);
			}
			if ($params['Action'] == 'SearchServer') {
			$aseco->releaseChatCommand('/elist ' .$params['Value']. '', $player->login);
			}
			if ($params['Action'] == 'SearchMX') {
			$aseco->releaseChatCommand('/xlist ' .$params['Value']. '', $player->login);
			}
			
			
			$aseco->client->query('SendDisplayManialinkPage', $xml, 0, false);
		}
		
		
	}
	
	

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	
	
	
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	
	public function buildbutton ($login=false) {
		
		global $aseco;

		$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<manialink id="'. $this->config['MANIALINK_ID'][0] .'01" version="3">';
		// $xml .= '<quad pos="'. $this->config['BUTTON'][0]['POSITION_X'][0] .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]) . '" z-index="1" size="15 5" style="Bgs1" substyle="BgCard" modulatecolor="30ABEDFF" action="PluginJukeAMap?Action=Pressed"/>';
		$xml .= '<label pos="'. $this->config['BUTTON'][0]['POSITION_X'][0] .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]) . '" z-index="1" size="20 5" text="Juke a Map" textsize="1.5" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=Pressed"/>';
		$xml .= '</manialink>';
		
		if ($login != false) {
			// Send to $login
			$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 0, false);
		}
		else {
			// Send to all Players
			$aseco->client->query('SendDisplayManialinkPage', $xml, 0, false);
		}
		
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	public function mapentries ($login) {
		
	global $aseco;
	
	$maniascript = <<<EOL
<script><!--

#Include "TextLib" as TextLib

main() {
	
	declare CMlEntry EntryMapName <=> (Page.GetFirstChild("EntryMapName") as CMlEntry);


	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonSearchServer") {
						TriggerPageAction("PluginJukeAMap?Action=SearchServer&Value="^ EntryMapName.Value);
					}
					if (Event.ControlId == "ButtonSearchMX") {
						TriggerPageAction("PluginJukeAMap?Action=SearchMX&Value="^ EntryMapName.Value);
					}
				}
			}	
		}
	}
}
--></script>
EOL;

	
		$xml =  '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<manialink id="'. $this->config['MANIALINK_ID'][0] .'02" version="3">';
		$xml .= '<quad pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+22.5) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]+0.2) . '" z-index="1" size="23.3 29.3" bgcolor="FFFA" style="Bgs1" substyle="BgWindow2" opacity="0.8"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]) . '" z-index="1.1" size="20 5" text="ALL" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=all"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-4) . '" z-index="1.1" size="20 5" text="Newest" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=newest"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-8) . '" z-index="1.1" size="20 5" text="worst" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=worst"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-12) . '" z-index="1.1" size="20 5" text="not finished" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=nofinish"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-16) . '" z-index="1.1" size="20 5" text="not recent" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=norecent"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-20) . '" z-index="1.1" size="20 5" text="no rank" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=norank"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-24) . '" z-index="1.1" size="20 5" text="show jukebox" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" action="PluginJukeAMap?Action=showjukebox"/>';
	
	
	
	
		$xml .= '<quad pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+22.5) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-29.7) . '" z-index="1" size="23.3 14" bgcolor="FFFA" style="Bgs1" substyle="BgWindow2" opacity="0.8"/>';
		$xml .= '<entry pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+34.1) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-32.4) . '" z-index="1.1" size="27.4 5" scale="0.8" textsize="1.3" halign="center" valign="center2" autonewline="0" id="EntryMapName" ScriptEvents="1"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-34.6) . '" z-index="1.1" size="20 5" text="Search Server" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" id="ButtonSearchServer" ScriptEvents="1"/>';
		$xml .= '<label pos="'. ($this->config['BUTTON'][0]['POSITION_X'][0]+23) .' '. ($this->config['BUTTON'][0]['POSITION_Y'][0]-38.6) . '" z-index="1.1" size="20 5" text="Search MX" style="CardButtonSmallS" textcolor="FFFFFFFF" scale="0.8" id="ButtonSearchMX" ScriptEvents="1"/>';
		
		
		$xml .= $maniascript;
			
			
		$xml .= '</manialink>';	
	
		
		
		
		// Send to $login
		$aseco->client->query('SendDisplayManialinkPageToLogin', $login, $xml, 14000, false);
		
	}
	
	
}

?>
