<?php

function folding_select_by_user_id($pdo, $user_id)
{
    $stmt = $pdo->prepare('SELECT * FROM folding_at_home WHERE user_id = :user_id LIMIT 1');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception('Could not perform query folding_select_by_user_id.');
    }

    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (empty($row)) {
        throw new Exception('Could not find a folding_at_home entry for user #$user_id.');
    }

    return $row;
}
