// Funciones para el manejo de observaciones
function openEditObservationsModal(id, observations) {
    const modal = document.getElementById('editObservationsModal');
    document.getElementById('edit_obs_id').value = id;
    document.getElementById('edit_observations').value = observations || '';
    modal.style.display = 'block';
}

function closeEditObservationsModal() {
    document.getElementById('editObservationsModal').style.display = 'none';
}

// Manejar el envío del formulario de observaciones
document.addEventListener('DOMContentLoaded', function() {
    const obsForm = document.getElementById('editObservationsForm');
    if (obsForm) {
        obsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('update_observations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar la celda de observaciones en la tabla
                    const id = formData.get('id_asistencia');
                    const button = document.querySelector(`.edit-obs-btn[data-id="${id}"]`);
                    if (button) {
                        const row = button.closest('tr');
                        const observationsText = row.querySelector('.observations-text');
                        const newObservations = formData.get('observaciones');
                        
                        observationsText.textContent = newObservations || 'Ninguna';
                        button.setAttribute('data-observations', newObservations || '');
                    }
                    
                    // Cerrar el modal
                    closeEditObservationsModal();
                    
                    // Mostrar mensaje de éxito
                    alert('¡Observaciones actualizadas correctamente!');
                } else {
                    throw new Error(data.message || 'Error al actualizar las observaciones');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar las observaciones: ' + error.message);
            });
        });
    }

    // Asignar manejadores de eventos a los botones de edición de observaciones
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-obs-btn')) {
            e.preventDefault();
            const button = e.target.closest('.edit-obs-btn');
            const id = button.getAttribute('data-id');
            const observations = button.getAttribute('data-observations');
            openEditObservationsModal(id, observations);
        }
    });
});
