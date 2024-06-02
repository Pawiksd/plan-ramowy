document.addEventListener('DOMContentLoaded', function() {
  const cells = document.querySelectorAll('.conference-day-schedule td[data-tooltip]');

  cells.forEach(cell => {
    cell.addEventListener('mouseenter', function() {
      const tooltipText = this.getAttribute('data-tooltip');
      if (!tooltipText) return;

      const tooltip = document.createElement('div');
      tooltip.className = 'custom-tooltip';
      tooltip.style.position = 'absolute';
      tooltip.style.left = (this.getBoundingClientRect().left + window.scrollX) + 'px';
      tooltip.style.top = (this.getBoundingClientRect().top + window.scrollY + this.offsetHeight) + 'px';
      tooltip.innerHTML = tooltipText;

      document.body.appendChild(tooltip);

      // Opóźnienie pozwala na zastosowanie efektu fadeIn
      setTimeout(() => {
        tooltip.style.opacity = 1;
      }, 10);

      this.addEventListener('mouseleave', function() {
        // Rozpoczęcie fadeOut
        tooltip.style.opacity = 0;
        // Usunięcie tooltipu po zakończeniu animacji fadeOut
        setTimeout(() => {
          tooltip.remove();
        }, 300); // Czas powinien odpowiadać czasowi trwania animacji CSS
      });
    });

    // Dodanie event listenera click
    cell.addEventListener('click', function() {
      const url = this.getAttribute('data-tooltip-url');
      if (url) {
        window.location.href = url;
      } else {
        console.error('Element nie posiada atrybutu data-tooltip-url');
      }
    });
  });
});
