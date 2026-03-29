<?php
/*
 * Plugin: Checkpoints
 * ~~~~~~~~~~~~~~~~~~~
 * » Displays best checkpoint times
 *
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----------------------------------------------------------------------------------
 *
 */


$_PLUGIN = new PluginCheckpointRecords();

class PluginCheckpointRecords extends Plugin
{
    public $config;

    private $cpCount;
    private $checkpoints = [];

    private $basePosX;
    private $basePosY;
    private $scale;
    private $spacing;
    private $type;
    private $columns;

    /**
     * PluginCheckpointRecords constructor.
     */
    public function __construct()
    {
//        require_once __DIR__ . '/plugin.checkpoint_records.ext.php';

        $this->setVersion('1.12.1');
        $this->setBuild('2018-11-17');
        $this->setAuthor('brakerb');
        $this->setCoAuthors('undef.de');
        $this->setCopyright('(c) Braker');
        $this->setDescription('Shows best time for each checkpoint.');

        $this->registerEvent('onSync', 'onSync');
        $this->registerEvent('onPlayerConnect', 'onPlayerConnect');
        $this->registerEvent('onPlayerCheckpoint', 'onPlayerCheckpoint');
        $this->registerEvent('onLoadingMap', 'onLoadingMap');
        $this->registerEvent('onEndRound', 'onEndRound');
        $this->registerEvent('onEndMatch', 'onEndMatch');

        $this->type = 'ONELINE';
    }

    /**
     * Called on sync
     * @param $aseco
     */
    public function onSync($aseco)
    {
        $this->cpCount = $this->getCheckpointCount($aseco);

        // Read Configuration
        if (!$xml = $aseco->parser->xmlToArray('config/checkpoint_records.xml', true, true)) {
            trigger_error('[LazyButtons] Could not read/parse config file "config/checkpoint_records.xml"!', E_USER_ERROR);
        }
        $this->config = $xml['SETTINGS'];
        unset($xml);

        $this->columns = $this->config['COLUMNS'][0];
        $this->basePosX = floatval($this->config['POS_X'][0]);
        $this->basePosY = floatval($this->config['POS_Y'][0]);
        $this->spacing = floatval($this->config['SPACING'][0]);
        $this->scale = floatval($this->config['SCALE'][0]);
    }

    /**
     * Resets all data and cleares widget
     * @param $aseco
     */
    private function initialize($aseco)
    {
        $this->checkpoints = [];
        $this->cpCount = $this->getCheckpointCount($aseco);
        $this->updateWidget($aseco);
    }

    /**
     * Cakked when a player connects
     * @param $aseco
     * @param $player
     */
    public function onPlayerConnect($aseco, $player)
    {
        $aseco->sendManialink($this->getManialink(), $player->login);
    }

    /**
     * Called when a map is loaded
     * @param $aseco
     * @param $map
     */
    public function onLoadingMap($aseco, $map)
    {
        $this->initialize($aseco);
    }

    /**
     * Called when a round ends
     * @param $aseco
     * @param $count
     */
    public function onEndRound($aseco, $count)
    {
        $this->initialize($aseco);
    }

    /**
     * Called when a match ends
     * @param $aseco
     * @param $count
     */
    public function onEndMatch($aseco, $count)
    {
        $this->initialize($aseco);
    }

    /**
     * Gets checkpoint count
     * @param $aseco
     * @return int
     */
    private function getCheckpointCount($aseco)
    {
        $checkpoints = intval($aseco->server->maps->current->nb_checkpoints);
        return $checkpoints;
    }

    /**
     * Sends the updated widget to the players
     * @param $aseco
     */
    public function updateWidget($aseco)
    {
        foreach ($aseco->server->players->player_list as $player) {
            $aseco->sendManialink($this->getManialink(), $player->login);
        }
    }

    /**
     * Called when player passes checkpoint
     * @param $aseco
     * @param $params
     */
    public function onPlayerCheckpoint($aseco, $params)
    {
        $player = $aseco->server->players->getPlayerByLogin($params['login']);

        if (!$player) return;

        $time = $params['lap_time'];
        $cpId = $params['checkpoint_in_race'];

	$force_update = false;
        if (!array_key_exists($cpId, $this->checkpoints)) {
		$this->checkpoints[$cpId] = new RecordsCheckpoint($cpId, $time, $player);
		$force_update = true;
        }

        $checkpoint = $this->checkpoints[$cpId];

        if ($checkpoint->getTime() > $time || $force_update === true ) {
            $checkpoint->setNewBest($time, $player);
            $this->updateWidget($aseco);
        }
    }

    /**
     * Get the Widget XML
     * @return string
     */
    private function getManialink()
    {
        $xml = '<manialink id="PluginCheckpointRecords" version="3">';

        $column = 0;
        $row = 0;

        for ($i = 1; $i <= $this->cpCount; $i++) {
            if (array_key_exists($i, $this->checkpoints)) {
                $checkpoint = $this->checkpoints[$i];

                if (!$checkpoint) continue;

                $xml .= $this->getTemplate($checkpoint, $row, $column);
            }

            $column++;

            if ($column >= $this->columns) {
                $row++;
                $column = 0;
            }
        }

        $xml .= '</manialink>';

        return $xml;
    }

    /**
     * Load checkpoint template with data
     * @param Checkpoint $checkpoint
     * @param $row
     * @param $column
     * @return mixed|string
     */
    private function getTemplate(RecordsCheckpoint $checkpoint, $row, $column)
    {
        switch ($this->type) {
            case 'ONELINE_NO_CP':
                $x = 90;
                $y = 10;
                $template = $this->versionOneLineNoCp();
                break;

            default:
            case 'ONELINE':
                $x = 110;
                $y = 10;
                $template = $this->versionOneLine();
                $template = str_replace('cp_time_white', $checkpoint->getTimeNotNull(), $template);
                break;
        }

        $posX = ($x + $this->spacing) * $this->scale * $column;
        $posY = ($y + $this->spacing) * $this->scale * $row;

        $template = str_replace('cp_id', $checkpoint->getId() . '.', $template);
        $template = str_replace('cp_time', $checkpoint->getTimeFormatted(), $template);
        $template = str_replace('cp_player', $checkpoint->getPlayerNick(), $template);

        $xml = '<frame pos="' . ($posX - $this->basePosX) . ' ' . ($this->basePosY - $posY) . '" scale="' . $this->scale . '">' . $template . '</frame>';

        return $xml;
    }

    //Template: One line with CP Id
    private function versionOneLine()
    {
        $xml = '<label pos="14.2 -2" z-index="0" size="7.81 5" scale="1.6" text="cp_id" halign="right" />
                <label pos="47 -2" z-index="0" size="20.4 5" scale="1.6" text="cp_time" halign="right" textcolor="F03" />
                <label pos="47 -2" z-index="0" size="20.4 5" scale="1.6" text="cp_time_white" halign="right" textcolor="F03" />
                <label pos="53 -1" z-index="0" size="27.9 5" scale="1.6" text="cp_player" halign="left" />

                <quad pos="0 0" z-index="-1" size="110 10" bgcolor="222233BB"/>
                <quad pos="50 0" z-index="-1" size="60 10" bgcolor="222233BB"/>';

        return $xml;
    }

    //Template: One line without CP Id
    private function versionOneLineNoCp()
    {
        $xml = '<label pos="32 -2" z-index="0" size="20.4 5" scale="1.6" text="cp_time" halign="right" />
                <label pos="38 -1" z-index="0" size="31.2 5" scale="1.6" text="cp_player" halign="left" />

                <quad pos="0 0" z-index="0" size="90 10" bgcolor="222233BB"/>
                <quad pos="35 0" z-index="-1" size="55 10" bgcolor="222233BB"/>';

        return $xml;
    }
}

class RecordsCheckpoint extends Checkpoint
{
    private $id;
    private $time;
    private $player;

    public function __construct($id = null, $time = null, $player = null)
    {
        parent::__construct();

        if ($id) $this->id = $id;
        if ($time) $this->time = $time;
        if ($player) $this->player = $player;
    }

    public function isBetter($time)
    {
        return $this->time > $time;
    }

    public function setNewBest($time, $player = null)
    {
        $this->time = $time;
        if ($player) {
            $this->player = $player;
        }
    }

    public function getTime()
    {
        return $this->time;
    }

    /**
     * Returns formatted time
     * @return string
     */
    public function getTimeFormatted()
    {
        $minutes = 0;
        $seconds = floor($this->time / 1000);
        $ms = $this->time % 1000;

        if ($seconds >= 60) {
            $minutes = $seconds / 60;
            $seconds = $seconds % 60;
        }

        return sprintf('%d:%02d.%03d', $minutes, $seconds, $ms);
    }

    public function getTimeNotNull()
    {
        $time = $this->getTimeFormatted();

        if (preg_match('/^([0:.]+)/', $time, $matches)) {
            return str_replace($matches[1], '', $time);
        }

        return $time;
    }

    public function getPlayerNick()
    {
        return $this->player->nickname;
    }

    /**
     * Gets checkpoint ID
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }
}

?>
