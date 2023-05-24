<!DOCTYPE html>
<html>
<head>
    <title>Task List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/css/bootstrap.min.css">
    <style>
    .task.completed {
        display: none;
    }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task List</h1>

        <form id="add-task" method="POST">
            @csrf
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="task-title" placeholder="Enter task title" aria-describedby="add-task-btn">
                <button type="submit" class="btn btn-primary" id="add-task-btn">Enter</button>
            </div>
        </form>

        <div class="mb-3">
            <button class="btn btn-secondary" id="show-all-tasks">Show All Tasks</button>
        </div>

        <div id="task-list">
            @foreach ($tasks as $task)
                <div class="task card mb-3 @if ($task->completed) completed @endif">
                    <div class="card-body">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input task-checkbox" data-task-id="{{ $task->id }}" @if ($task->completed) checked @endif>
                            <label class="form-check-label task-title">{{ $task->title }}</label>
                        </div>
                        <span class="task-status">{{ $task->completed ? 'Completed' : 'Incomplete' }}</span>
                        <button class="btn btn-danger delete-button" data-task-id="{{ $task->id }}">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show all tasks
            $('#show-all-tasks').click(function() {
                $('.task').show();
            });

            // Add new task
            $('#add-task').submit(function(event) {
                event.preventDefault();
                var title = $('#task-title').val();

                $.ajax({
                    url: '{{ route("tasks.store") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'title': title
                    },
                    success: function(response) {
                        $('#task-title').val('');
                        $('#task-list').append('<div class="task card mb-3">' +
                            '<div class="card-body">' +
                            '<div class="form-check">' +
                            '<input type="checkbox" class="form-check-input task-checkbox" data-task-id="' + response.task_id + '">' +
                            '<label class="form-check-label task-title">' + title + '</label>' +
                            '</div>' +
                            '<button class="btn btn-danger delete-button" data-task-id="' + response.task_id + '">Delete</button>' +
                            '</div>' +
                            '</div>');
                    }
                });
            });

            // Complete task

            $(document).on('change', '.task-checkbox', function() {
                var checkbox = $(this);
                var taskId = checkbox.data('task-id');
                var taskCard = checkbox.closest('.task');

                $.ajax({
                    url: '/tasks/' + taskId + '/complete',
                    type: 'PUT',
                    dataType: 'json',
                    data: {
                        '_token': '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if (response.success) {
                            if (checkbox.is(':checked')) {
                                taskCard.addClass('completed');
                                taskCard.find('.task-status').text('Completed');
                            } else {
                                taskCard.removeClass('completed');
                                taskCard.find('.task-status').text('Incomplete');
                            }
                            alert(response.message);
                        } else {
                            alert(response.error);
                        }
                    }
                });
            });

            // Delete task
            $(document).on('click', '.delete-button', function() {
                var deleteButton = $(this);
                var taskId = deleteButton.data('task-id');
                var taskCard = deleteButton.closest('.task');

                if (confirm("Are you sure to delete this task?")) {
                    $.ajax({
                        url: '/tasks/' + taskId,
                        type: 'DELETE',
                        dataType: 'json',
                        data: {
                            '_token': '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response.success) {
                                taskCard.slideUp('normal', function() {
                                    taskCard.remove();
                                });
                            } else {
                                alert(response.error);
                            }
                        }
                    });
                }
            });

        });
    </script>
</body>
</html>
