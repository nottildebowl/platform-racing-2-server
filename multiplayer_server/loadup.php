<?php

require_once __DIR__ . '/../http_server/queries/servers/server_select.php';
require_once __DIR__ . '/../http_server/queries/campaign/campaign_select.php';
require_once __DIR__ . '/../http_server/queries/purchases/purchases_select_recent.php';
require_once __DIR__ . '/../http_server/queries/artifact_locations/artifact_location_select.php';
require_once __DIR__ . '/../http_server/queries/guilds/guild_select.php';


function begin_loadup($server_id)
{
    global $pdo, $server_id;

    $server = server_select($pdo, $server_id);
    $campaign = campaign_select($pdo);
    $perks = purchases_select_recent($pdo);
    $artifact = artifact_location_select($pdo);

    set_server($pdo, $server);
    set_campaign($campaign);
    set_perks($perks);
    place_artifact($artifact);
    if ($server_id == 2) {
        HappyHour::activate();
    }
}



function set_server($pdo, $server)
{
    global $port, $server_name, $uptime, $server_expire_time, $guild_id, $guild_owner, $key;
    $port = $server->port;
    $server_name = $server->server_name;
    $datetime = new DateTime();
    $uptime = $datetime->format('Y-m-d H:i:s P');
    $server_expire_time = $server->expire_date;
    $guild_id = $server->guild_id;
    $guild_owner = 0;
    $key = $server->salt;
    pr2_server::$tournament = $server->tournament;
    if (pr2_server::$tournament) {
        pr2_server::$no_prizes = true;
    }

    if ($guild_id != 0) {
        $guild = guild_select($pdo, $guild_id);
        $guild_owner = $guild->owner_id;
    } else {
        $guild_owner = 4291976; //Fred the G. Cactus
    }
}



function set_campaign($campaign_levels)
{
    global $campaign_array;
    $campaign_array = array();
    foreach ($campaign_levels as $level) {
        $campaign_array[$level->level_id] = $level;
    }
}



function set_perks($perks)
{
    foreach ($perks as $perk) {
        $slug = $perk->product;
        $a = array( Perks::GUILD_FRED, Perks::GUILD_GHOST );
        if (array_search($slug, $a) !== false) {
            output("activating perk $slug for user $perk->user_id and guild $perk->guild_id");
            start_perk($slug, $perk->user_id, $perk->guild_id);
        }
        if ($slug == 'happy-hour') {
            HappyHour::activate();
        }
    }
}
