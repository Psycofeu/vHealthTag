<?php

namespace psycofeu\healthTag;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class Main extends PluginBase implements Listener
{
    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->notice("Plugin enable");
    }
    public function onJoin(PlayerJoinEvent $event)
    {
        $this->setScoreTag($event->getPlayer());
    }
    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof Player){
            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                $this->setScoreTag($player);
            }), 1);
        }

    }
    public function getScoreTag(): string
    {
        $type = $this->getConfig()->get("heath_type");
        return strtolower($type);
    }
    public function respawnEvent(PlayerRespawnEvent $event)
    {
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($event): void {
            $this->setScoreTag($event->getPlayer());
        }), 1);
    }
    public function setScoreTag(Player $player)
    {
        $config = $this->getConfig();
        $heal = (int)$player->getHealth();
        $max = $player->getMaxHealth();
        switch ($this->getScoreTag()){
            default:
                $player->setScoreTag($config->get("bar_color")[0] . str_repeat("|", $heal) . $config->get("bar_color")[1] . str_repeat("|", $max-$heal));
                break;
            case "percent":
                $player->setScoreTag(str_replace("{percent}", $heal/$max * 100, $config->get("percent_color")));
                break;
            case "health":
                $player->setScoreTag(str_replace("{life}", $heal, $config->get("health_color")));
                break;
        }
    }
}
