<?php
require_once 'db.php';

$errors = [];
$taskName = '';
$taskDescription = '';
$editingId = null;

// Validation shared by add and update
function validateTask($name, $description)
{
    $errors = [];

    if ($name === '') {
        $errors[] = "Task name is required.";
    } elseif (strlen($name) < 3) {
        $errors[] = "Task name must be at least 3 characters.";
    } elseif (strlen($name) > 255) {
        $errors[] = "Task name cannot exceed 255 characters.";
    }

    if ($description === '') {
        $errors[] = "Task description is required.";
    } elseif (strlen($description) > 255) {
        $errors[] = "Task description cannot exceed 255 characters.";
    }

    return $errors;
}

// Handle POST requests (all state-changing actions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

    // CREATE
    if ($action === 'add') {
        $taskName = trim($_POST['task_name'] ?? '');
        $taskDescription = trim($_POST['task_description'] ?? '');
        $errors = validateTask($taskName, $taskDescription);

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO tasks (task_name, task_description) VALUES (?, ?)"
            );
            $stmt->execute([$taskName, $taskDescription]);
            header("Location: index.php");
            exit();
        }
    }

    // UPDATE (edit name and description)
    if ($action === 'update') {
        $taskName = trim($_POST['task_name'] ?? '');
        $taskDescription = trim($_POST['task_description'] ?? '');
        $errors = validateTask($taskName, $taskDescription);

        if ($id === false) {
            $errors[] = "Invalid task selected.";
        } else {
            $editingId = $id; // stay in edit mode if validation fails
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "UPDATE tasks SET task_name = ?, task_description = ? WHERE id = ?"
            );
            $stmt->execute([$taskName, $taskDescription, $id]);
            header("Location: index.php");
            exit();
        }
    }

    // UPDATE (toggle pending/completed)
    if ($action === 'toggle' && $id !== false) {
        $stmt = $pdo->prepare(
            "UPDATE tasks SET status = IF(status = 'pending', 'completed', 'pending') WHERE id = ?"
        );
        $stmt->execute([$id]);
        header("Location: index.php");
        exit();
    }

    // DELETE
    if ($action === 'delete' && $id !== false) {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php");
        exit();
    }
}

// Load a task into the form when ?edit=ID (read-only, so GET is fine)
if (isset($_GET['edit']) && $editingId === null) {
    $selectedId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($selectedId !== false) {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$selectedId]);
        $task = $stmt->fetch();

        if ($task) {
            $editingId = (int) $task['id'];
            $taskName = $task['task_name'];
            $taskDescription = $task['task_description'];
        }
    }
}

// READ: fetch all tasks
$tasks = $pdo->query("SELECT * FROM tasks ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-do List</title>
    <link rel="stylesheet" href="style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
    <main>
        <h1>My To-do List</h1>
        <p>Organize your tasks and stay productive.</p>

        <!-- Task Form -->
        <form method="POST" action="">
            <?php if ($editingId !== null): ?>
                <h2>Edit Task</h2>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $editingId ?>">
            <?php else: ?>
                <h2>Add New Task</h2>
                <input type="hidden" name="action" value="add">
            <?php endif; ?>

            <label for="task_name">Task Name</label>
            <input
                type="text"
                id="task_name"
                name="task_name"
                value="<?= htmlspecialchars($taskName) ?>"
                required
            >

            <label for="task_description">Task Description</label>
            <textarea
                id="task_description"
                name="task_description"
                required
            ><?= htmlspecialchars($taskDescription) ?></textarea>

            <button type="submit">
                <?= $editingId !== null ? 'Update Task' : 'Add Task' ?>
            </button>

            <?php if ($editingId !== null): ?>
                <a href="index.php">Cancel Edit</a>
            <?php endif; ?>
        </form>

        <!-- Validation Errors -->
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <!-- Task List -->
        <section>
            <h2>My Tasks</h2>

            <?php if (empty($tasks)): ?>
                <p>No tasks yet. Add your first task!</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task <?= $task['status'] === 'completed' ? 'completed' : '' ?>">
                        <h3><?= htmlspecialchars($task['task_name']) ?></h3>
                        <p><?= htmlspecialchars($task['task_description']) ?></p>
                        <p class="status">Status: <?= htmlspecialchars($task['status']) ?></p>

                        <a href="index.php?edit=<?= (int) $task['id'] ?>">Edit</a>

                        <form method="POST" action="" class="toggle-form">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                            <button type="submit" class="toggle-button">
                                <?= $task['status'] === 'pending' ? 'Mark Completed' : 'Mark Pending' ?>
                            </button>
                        </form>

                        <form method="POST" action="" class="delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                            <button type="submit" class="delete-button">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
