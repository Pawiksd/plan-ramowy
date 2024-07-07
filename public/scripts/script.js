document.addEventListener('DOMContentLoaded', function() {
  const cells = document.querySelectorAll('.conference-day-schedule div[data-tooltip]');

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
/*

document.addEventListener("DOMContentLoaded", function() {
  if (typeof InstallTrigger !== 'undefined') { // Check if the browser is Firefox
    var cells = document.querySelectorAll("td.wss-nb");

    cells.forEach(function(cell) {
      var rowspan = cell.getAttribute("rowspan");
      if (rowspan && rowspan > 1) {
        var cellHeight = cell.offsetHeight;
        var childDiv = cell.querySelector("div");

        if (childDiv) {
          childDiv.style.height = cellHeight + "px";
          childDiv.style.position = "static"; // Ensure position is reset if previously set to absolute
        }
      }
    });
  }
});
*/
