<?php


function drawDepartmentTable(array $departments)
{
    ?>
    <table class="department-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Members</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </thead>

        <tbody class="department-tbody">
            <?php foreach ($departments as $department) :?>
                <tr class="department-entry">
                    <td>
                        <p class="department-name"><?php echo htmlspecialchars($department->name)?></p>
                    </td>
                    <td>
                        <p class="department-description"><?php echo htmlspecialchars($department->description)?></p>
                    </td>
                    <td>
                        <div class="department-members">
                            <?php
                            if ($department->count === 0) {
                                echo "0 members";
                            } ?>

                            <header>
                                <?php
                                foreach ($department->clients as $member) : ?>
                                <img src="<?php echo $member->image?>" alt="<?php echo $member->displayName?>" class="department-member">
                                <?php endforeach;?>
                            </header>
                            <?php if ($department->count !== 0) : ?>
                            <ul>
                                <?php
                                foreach ($department->clients as $member) : ?>
                                <li>
                                    <div>
                                        <img src="<?php echo $member->image?>" alt="<?php echo $member->displayName?>" class="department-member">
                                        <p><?php echo $member->displayName?></p>
                                    </div>
                                    <form method="post" action="/admin">
                                        <input type="hidden" name="department" value="<?php echo $department->name?>">
                                        <input type="hidden" name="user" value="<?php echo $member->username?>">
                                        <input type="hidden" name="action" value="removeMember">
                                        <input type="hidden" name="lastHref" value="/admin?tab=departments">
                                        <i class="ri-close-line" onclick="submitFatherForm(this)"></i>
                                    </form>
                                </li>
                                <?php endforeach;?>
                            </ul>
                            <?php endif;?>
                        </div>
                    </td>
                    <td>
                        <button class="white" onclick="makeUserModal('<?php echo $department->name?>')">
                            <i class="ri-user-add-line"></i>
                            Add member
                        </button>
                    </td>
                    <td>
                        <i class="ri-edit-line icon" onclick="makeEditModal('<?php echo htmlspecialchars($department->name)?>')"></i>
                    </td>
                    <td>
                        <i class="ri-delete-bin-line icon delete" onclick="makeDeleteModal('<?php echo htmlspecialchars($department->name)?>')")></i>
                    </td>
                </tr>
                <?php
            endforeach;?>






















































        </tbody>
    </table>
    <?php

}
