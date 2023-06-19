<?php


function drawUserTable(array $clients)
{
    ?>
<table class="user-table">
<thead>
    <tr>
        <th>
            User
        </th>
        <th>
            Email
        </th>
        <th>
            Role
        </th>
        <th>
            Date created
        </th>
        <th>
            </th>
            <th>
                <div class="dropdown-hover">
                    <i class="ri-filter-line icon">
                    </i>
                    <div class="dropdown-content role-filter">
                            <h3>Sort by role:</h3>
                            <a href="?sort=client" >Client</a>
                            <a href="?sort=agent" >Agent</a>
                            <a href="?sort=admin" >Admin</a>
                    </div>
                </div>

            </th>
    </tr>
</thead>
<tbody class="user-table-body">
    <?php foreach ($clients as $client) :?>
    <tr class="user-entry">
        <td class="user-info">
            <img class="user-photo" src="<?php echo htmlspecialchars($client->image)?>" alt="user">
            <div class="user-name">
                <p><?php echo htmlspecialchars($client->displayName)?></p>
                <p><?php echo htmlspecialchars($client->username)?></p>
            </div>
        </td>
        <td>
            <a href="mailto:<?php echo htmlspecialchars($client->email)?>" ><?php echo htmlspecialchars($client->email)?></a>
        </td>
        <td>
            <p class="role <?php echo htmlspecialchars($client->type)?>"><?php echo htmlspecialchars(ucfirst($client->type))?></p>
        </td>
        <td>
            <p><?php
                $dateTime = date_create("@".$client->createdAt);
                $dateTime->setTimezone(new DateTimeZone("Europe/Lisbon"));
                echo $dateTime->format("H:i d/m/o");
            ?></p>
        </td>
        <td>
            <i class="ri-edit-line icon" onclick="makeEditModal('<?php echo htmlspecialchars($client->username)?>')"></i>
        </td>
        <td>
            <i class="ri-delete-bin-line icon delete" onclick="makeDeleteModal('<?php echo htmlspecialchars($client->username)?>')"></i>
        </td>
    </tr>
    <?php endforeach;?>

    
</tbody>
</table>
    <?php

}
