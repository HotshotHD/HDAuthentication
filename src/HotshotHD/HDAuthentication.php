<?php
namespace HotshotHD;
use pocketmine\plugin\PluginBase;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\event\Listener;

use pocketmine\event\Player;

use pocketmine\utils\Config;
class HDAuthentication extends PluginBase implements Listener {
	
	public $tries = array();
	public $temppass = array();
	public $NotAuthenticated = array();
	public $Authenticated = array();
	public $Registered = array();
	public $NotRegistered = array();
	
	public function onEnable() {
		$this->getLogger()->info("Enabled.");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder() . "Users/");
	}

	public function isAuthenticated($playername) {
		return in_array($playername, $this->Authenticated);
	}
	
	public function isNotAuthenticated($playername) {
		return in_array($playername, $this->NotAuthenticated);
	}
	
	public function authenticatePlayer($player, $playername) {
		unset($this->NotAuthenticated[$player->getName()]);
		$this->Authenticated[$playername] = $playername;
	}
	
	public function unAuthenticatePlayer($player) {
		unset($this->Authenticated[$player->getName()]);
	}
	
	public function setNotAuthenticated($playername) {
		unset($this->Authenticated[$playername]);
	    $this->NotAuthenticated[$playername] = $playername;
	}
	
	public function unRegisterPlayer($player) {
		$this->player->set("Registered", "false");
	}
	
	public function isRegistered() {
		return $this->player->get("Registered") == "true";
	}
	
	public function isNotRegistered() {
		return $this->player->get("Registered") == "false";
	}
	
	public function tempRegisterPlayer($player, $playername, $message, $event) { // TEMPORARY REGISTER FUNCITON
		unset($this->NotRegistered[$player->getName()]);
		$this->player->set("Registered", "true");
		$this->player->set("Password", password_hash($message, PASSWORD_DEFAULT));
		$this->player->save();
		$this->authenticatePlayer($player, $playername);
		$player->sendMessage("§aYou have been successfully registered!");
		$event->setCancelled();
		
	}
	/* NOT IMPLEMTED YET...
	public function registerPlayer($player, $message, $playername, $event) {
		
		$event->setMessage(".");
		$this->temppass[$playername] = password_hash($event->getMessage(), PASSWORD_DEFAULT);
		$player->sendMessage("§cRepeat password to confirm.");
		$event->setCancelled(true);
		
		if(password_verify($message, $message) === $this->temppass[$playername]) {	
        unset($this->temppass[$playername]);
		$this->authenticatePlayer($player, $playername);
		$this->player->set("Password", password_hash($message));
		$this->player->set("Registered", "true");
		$this->player->save();
		$player->sendMessage("§aYou have been successfully registered!");
		}
		else {
			$player->sendMessage("§cPasswords do not match! Try again.");
			$event->setCancelled(true);
		}
	}
	*/
	
	public function setNotRegistered($playername) {
		$this->NotRegistered[$playername] = $playername;
	}
	
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$playername = $player->getName();
		$this->player = new Config($this->getDataFolder() . "Users/" . strtolower($player->getName()) . ".yml", Config::YAML, array(
		"Password" => "NoPassword",
		"Registered" => "false"
		));	
		$password = $this->player->get("Password");
		
		$this->setNotAuthenticated($playername);
		if($password == "NoPassword") {
			$this->setNotRegistered($playername);
		}
		
		if($this->isNotAuthenticated($playername) && $this->isRegistered($playername)) {
			$player->sendMessage("§cYou are not authenticated. Please type your password:");
		}
		
		if($this->isNotRegistered()) {
			$player->sendMessage("§cYou are not registered. Please type your desired password:");
		}
	}
	
	public function onChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();
		$playername = $player->getName();
		$this->player = new Config($this->getDataFolder() . "Users/" . strtolower($player->getName()) . ".yml", Config::YAML, array(
		"Password" => "NoPassword"
		));	
		$password = $this->player->get("Password");
		$message = $event->getMessage();
		
		if($this->isNotAuthenticated($playername) && $this->isRegistered()) {	
		    $event->setCancelled(true);
			
			if(password_verify($message, $password) == $password) {
			$this->authenticatePlayer($player, $playername);
			$player->sendMessage("§aYou have been authenticated!");
		}
		else {
			$player->sendMessage("§cIncorrect password!");
		}
		}
		
		if($this->isNotRegistered($player)) {
			if(preg_match('/\s/', $message)) {
				$event->setCancelled(true);
				$player->sendMessage("§cPassword cannot contain any spaces!");
			}
            $this->tempRegisterPlayer($player, $playername, $message, $event);			
			$event->setCancelled(true);
			}
		
		if($this->isAuthenticated($player)) {
			$event->setCancelled(false);
		}
		
		
	}
	
	public function onMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		$playername = $player->getName();
		
		if($this->isNotAuthenticated($playername) || $this->isNotRegistered()) {
			$event->setCancelled(true);
		}
	}
	
	public function onDamage(EntityDamageEvent $event) {
		$player = $event->getEntity();
		$playername = $player->getName();
		$cause = $player->getLastDamageCause();
		
		/*
		if($cause instanceof EntityDamageByEntityEvent && $cause instanceof Player) {
			$attacker = $cause->getDamager();
			if($this->isNotAuthenticated($attacker) || $this->isNotAuthenticated($attacker)) {
				$event->setCancelled();
			}
		}
		*/
		
		if($this->isNotAuthenticated($playername)) {
			$event->setCancelled(true);
		}
	}
}
	
	
?>