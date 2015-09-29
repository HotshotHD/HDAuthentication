<?php
namespace HotshotHD;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
class HDAuthentication extends PluginBase implements Listener {
	
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
		return in_array($playername, $this->isAuthenticated);
	}
	
	public function isNotAuthenticated($playername) {
		return in_array($playername, $this->NotAuthenticated);
	}
	
	public function authenticatePlayer($player) {
		unset($this->isNotAuthenticated[$player->getName()]);
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
	
	public function isRegistered($playername) {
		$this->player->get("Registered") == "true";
	}
	
	public function isNotRegistered($player) {
		$this->player->get("Registered") == "false";
	}
	
	public function registerPlayer($playername) {
		$this->player->set("Resgistered", "true");
		$this->player->save();
		new Config($this->getDataFolder() . "Users/" . strtolower($player->getName()) . ".yml", Config::YAML, array(
		"Password" => "NoPassword",
		"Registered" => "false"
		));
	}
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
		
		$this->setNotAuthenticated($playername); // Resets players authentication status when they join
		
		if($password == "NoPassword") {
			$this->setNotRegistered($playername);
		}
		
		if($this->isNotAuthenticated($playername) && $this->isRegistered($playername)) {
			$player->sendMessage("§cYou are not authenticated. Please type your password:");
		}
		
		if($this->isNotRegistered($player)) {
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
		
		if($this->isNotAuthenticated($playername) && $this->isRegistered($playername)) {	
			if($message == $password) {
			$this->authenticatePlayer($player);
			$player->sendMessage("§aYou have been authenticated!");
		}
		else {
			$player->sendMessage("§cIncorrect password!");
		}
			$event->setCancelled(true);
		}
		
		if($this->isNotRegistered($player)) {
			if(preg_match('/\s/', $message)) {
				$player->sendMessage("§cPassword cannot contain any spaces!");
			}
			else {
			$this->player->set("Password", $message);
			$this->player->save();
            $this->registerPlayer($player);			
			$event->setCancelled(true);
			}
		}
		
		if($this->isAuthenticated($player)) {
			$event->setCancelled(false);
		}
		
		
	}
}
	
	
?>
