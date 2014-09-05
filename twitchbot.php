#!/usr/bin/php
<?php
$server = "irc.twitch.tv";
$port = 6667;
$nick = "TwitchNick"; // Enter your nick here.
$password = "oauth:xxxxxouathstringxxxxx"; // We might want to get this through twitch API asking the user for username and password.
$channels = array("#channel1", "#channel2");

/* End of configuration */

$fp = fsockopen($server, $port, $errno, $errstr);
if (!$fp) {echo "Error: $errno - $errstr\n"; exit;}
fwrite($fp, "PASS ".$password."\r\n");
fwrite($fp, "NICK ".$nick."\r\n");
while (!preg_match("/:\S+ 376 \S+ :.*/i", $read)) {
$read = fgets($fp);
}
foreach($channels as $num => $chan) {
fwrite($fp, "JOIN $chan\r\n");
}
echo "Connected!\n";
while (TRUE)
{
$read = fgets($fp);
if (preg_match("/:(\S+)!\S+@\S+ JOIN (#\S+)/i", $read, $match)) { user_joined($match[1], $match[2]); }
if (preg_match("/:(\S+)!\S+@\S+ PART (#\S+)/i", $read, $match)) { user_parted($match[1], $match[2]); }
if (preg_match("/:(\S+)!\S+@\S+ PRIVMSG (#\S+) :(.*)/i", $read, $match)) { inc_message($match[1], $match[2], $match[3]); }
if (preg_match("/:jtv!jtv@\S+ PRIVMSG $nick :(\S+)/i", $read, $match)) {jtv_error($match[1]);}
if (preg_match("/PING :.*/i", $read, $match)) { fwrite($fp, "PONG :$match[1]\r\n"); }
}
function user_joined($nick, $chan) {
global $users;
$users[$chan][] = $nick;
echo "$nick joined {$chan}.\n";
}
function user_parted($nick, $chan) {
global $users;
$num = array_search($nick, $users[$chan]);
if ($num !== FALSE) { unset($users[$chan][$num]); }
echo "$nick parted {$chan}.\n";
}
function inc_message($nick, $chan, $msg) {
	global $fp, $users;
	echo "$chan : <$nick> $msg\n";
	if (preg_match("/.*!usercount.*/mi", $msg)) {
		echo "!usercount triggered.\n";
		echo "$fp\n";
		echo "$chan\n";
		fwrite($fp, "PRIVMSG $chan :There are ".count($users[$chan])." users in this chatroom.\r\n");
	}
}
function jtv_error($msg) {
echo "Message from jtv: $msg\n";
}
