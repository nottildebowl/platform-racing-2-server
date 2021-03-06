<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../fns/output_fns.php';
require_once __DIR__ . '/../queries/users/users_select_top.php';

$start = (int) default_val($_GET['start'], 0);
$count = (int) default_val($_GET['count'], 100);
$group_colors = ["7e7f7f", "047b7b", "1c369f", "870a6f"];
$ip = get_ip();

try {
    // rate limiting
    rate_limit("leaderboard-" . $ip, 5, 2);

    // connect
    $pdo = pdo_connect();

    // header, also check if mod and output the mod links if so
    $is_mod = is_moderator($pdo, false);
    output_header('Leaderboard', $is_mod);

    // limit amount of entries to be obtained from the db at a time
    if ($is_mod === true) {
        if (($count - $start) > 100) {
            $count = 100;
        }
    } elseif ($is_mod === false) {
        rate_limit('leaderboard-'.$ip, 60, 10, 'Please wait at least one minute before trying to view the leaderboard again.');
        if (($count - $start) > 50) {
            $count = 50;
        }
    } else {
        throw new Exception("Could not determine user staff boolean.");
    }

    $users = users_select_top($pdo, $start, $count);

    echo '
	<center>
	<font face="Gwibble" class="gwibble">-- Leaderboard --</font>
	<br /><br />
	<table>
		<tr>
			<th>Username</th>
			<th>Rank</th>
			<th>Hats</th>
		</tr>
	';

    foreach ($users as $user) {
        // name
        $name = $user->name;
        $safe_name = htmlspecialchars($name);
        $safe_name = str_replace(" ", "&nbsp;", $safe_name);

        // group
        $group = (int) $user->power;
        $group_color = $group_colors[$group];

        // rank
        $rank = $user->active_rank;

        // hats
        $hat_array = $user->hats;
        $hats = count(explode(',', $hat_array))-1;

        // player details link
        $url_name = urlencode($name);
        $info_link = "player_search.php?name=$url_name";

        // echo the row
        echo "<tr>";

        echo "<td><a href='$info_link' style='color: #$group_color; text-decoration: underline;'>$safe_name</a></td>";
        echo "<td>$rank</td>";
        echo "<td>$hats</td>";

        echo "</tr>";
    }

    echo "</table>";
    output_pagination($start, $count);
    echo "</center>";

    output_footer();
} catch (Exception $e) {
    $error = $e->getMessage();
    $safe_error = htmlspecialchars($error);
    echo "Error: $safe_error";
    output_footer();
}

function output_pagination($start, $count)
{
    $next_start_num = $start + $count;
    $last_start_num = $start - $count;
    if ($last_start_num < 0) {
        $last_start_num = 0;
    }
    echo('<p>');
    if ($start > 0) {
        echo("<a href='?start=$last_start_num&count=$count'><- Last</a> |");
    } else {
        echo('<- Last |');
    }
    echo(" <a href='?start=$next_start_num&count=$count'>Next -></a></p>");
}
