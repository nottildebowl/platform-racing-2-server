<?php

function contest_activate($pdo, $contest_id)
{
    $stmt = $pdo->prepare('
        UPDATE contests
           SET active = 1,
               updated = :updated
         WHERE active = 0
           AND contest_id = :contest_id
    ');
    $stmt->bindValue(':updated', time(), PDO::PARAM_INT);
    $stmt->bindValue(':contest_id', $contest_id, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if ($result === false) {
        throw new Exception("Could not activate contest #$contest_id.");
    }
    
    return true;
}
