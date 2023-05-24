<!DOCTYPE html>
<html>
<head>
    <title>Task List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
    .task.completed {
        display: none;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="border border-dark p-4 bg-light">
                    <div class="container">
                        <h1 style="margin-bottom: 30px;">Task List</h1>
                        <div class="mb-3 form-check">
                            <button class="btn btn-primary" style="margin-bottom: 20px;" id="show-all-tasks">Show All Tasks</button>
                        </div>
                        <form id="add-task" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="task-title" placeholder="Enter task title" aria-describedby="add-task-btn" style="margin-bottom: 20px;">
                                </div>
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-primary" style="margin-bottom: 20px;" id="add-task-btn">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Checkbox</th>
                    <th scope="col">Task</th>
                    <th scope="col">Status</th>
                    <th scope="col">Delete</th>
                </tr>
            </thead>
            <tbody id="task-table-body">
                @foreach ($tasks as $task)
                <div class="task card mb-3 @if ($task->completed) completed @endif">
                    <tr>
                        <th scope="row">
                            <input type="checkbox" class="form-check-input task-checkbox" data-task-id="{{ $task->id }}" @if ($task->completed) checked @endif>
                        </th>
                        <td>
                            <label class="form-check-label task-title">{{ $task->title }}</label>
                        </td>
                        <td>
                            <span class="task-status">{{ $task->completed ? 'Completed' : 'Incomplete' }}</span>
                        </td>
                        <td>
                            <button class="btn btn-danger delete-button" data-task-id="{{ $task->id }}">Delete</button>
                        </td>
                    </tr>
                </div>
                @endforeach
            </tbody>        
        </table>
    </div>
    <div id="notifications" style="position: fixed; top: 20px; right: 20px;"></div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
    
            // Show all tasks 
            $('#show-all-tasks').click(function() {
                var completedTasks = $('#task-table-body').find('.completed');
                if (completedTasks.is(':visible')) {
                    completedTasks.hide();
                } else {
                    completedTasks.show();
                }
            });
    
            // Add new task
            $('#add-task').submit(function(event) {
                event.preventDefault();
                var title = $('#task-title').val();
                if (title.trim() === '') {
                    Swal.fire('Error', 'Task title cannot be empty.', 'error');
                    return;
                }
                // Check if the task already exists
                var existingTask = $('.task-title').filter(function() {
                    return $(this).text() === title;
                });
                if (existingTask.length > 0) {
                    Swal.fire('Error', 'Task already exists.', 'error');
                    return;
                }
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
                        $('#task-table-body').append('<tr>' +
                            '<td>' +
                            '<input type="checkbox" class="form-check-input task-checkbox" data-task-id="' + response.task_id + '">' +
                            '</td>' +
                            '<td>' +
                            '<label class="form-check-label task-title">' + title + '</label>' +
                            '</td>' +
                            '<td>' +
                            '<span class="task-status">' + (response.completed ? 'Completed' : 'Incomplete') + '</span>' +
                            '</td>' +
                            '<td>' +
                            '<button class="btn btn-danger delete-button" data-task-id="' + response.task_id + '">Delete</button>' +
                            '</td>' +
                            '</tr>');
                        Swal.fire('Success', 'Task added successfully.', 'success');
                    }
                });
            });
    
            // Complete task
            $(document).on('change', '.task-checkbox', function() {
                var checkbox = $(this);
                var taskId = checkbox.data('task-id');
                var taskRow = checkbox.closest('tr');
    
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
                                taskRow.addClass('completed');
                                taskRow.find('.task-status').text('Completed');
                                taskRow.hide(); // Hide the completed task
                            } else {
                                taskRow.removeClass('completed');
                                taskRow.find('.task-status').text('Incomplete');
                                taskRow.show(); // Show the incomplete task
                            }
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    }
                });
            });
    
            // Delete task
            $(document).on('click', '.delete-button', function() {
                var deleteButton = $(this);
                var taskId = deleteButton.data('task-id');
                var taskRow = deleteButton.closest('tr');
    
                Swal.fire({
                    title: 'Confirmation',
                    text: 'Are you sure to delete this task?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/tasks/' + taskId,
                            type: 'DELETE',
                            dataType: 'json',
                            data: {
                                '_token': '{{ csrf_token() }}',
                            },
                            success: function(response) {
                                if (response.success) {
                                    taskRow.slideUp('normal', function() {
                                        taskRow.remove();
                                    });
                                    Swal.fire('Success', response.message, 'success');
                                } else {
                                    Swal.fire('Error', response.error, 'error');
                                }
                            }
                        });
                    }
                });
            });
    
        });
    </script>
    
</body>
</html>
