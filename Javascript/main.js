document.querySelector('select[name="periodo"]').addEventListener('change', function() {
            const personalizado = document.getElementById('filtroPersonalizado');
            if (this.value === 'personalizado') {
                personalizado.classList.remove('hidden');
            } else {
                personalizado.classList.add('hidden');
            }
        });

        // Função para exportar PDF - BACKEND: Chamar script de PDF
function exportarPDF() {
            // BACKEND: Redirecionar para script de geração de PDF
            // window.location.href = 'exportar_pdf.php?' + new URLSearchParams(new FormData(form));
            
    alert('Exportando para PDF...');
            // Na implementação real, isso chamaria o backend para gerar o PDF
}


