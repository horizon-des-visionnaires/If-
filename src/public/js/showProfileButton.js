document.addEventListener('DOMContentLoaded', function () {
        const toggleButton2 = document.getElementById('toggleEditButton2');
        const Edit = document.getElementById('Toshow');
        const Edit2 = document.getElementById('Toshow2');
        const Edit3 = document.getElementById('Toshow3');

        function toggleEdit(event) {
            event.stopPropagation();
            Edit2.classList.toggle('show');
            Edit3.classList.toggle('show');
            if (Edit) {
                Edit.classList.toggle('show');
            }
        }
        toggleButton2.addEventListener('click', toggleEdit);

        document.addEventListener('click', function (event) {
            if (!Edit.contains(event.target) && !toggleButton2.contains(event.target)) {
                Edit2.classList.remove('show');
                Edit3.classList.remove('show');
                if (Edit) {
                    Edit.classList.remove('show');
                }
            }
        });
    });