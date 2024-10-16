document.addEventListener('DOMContentLoaded', function() {
    // Activity chart animation
    const bars = document.querySelectorAll('.bar');
    bars.forEach((bar, index) => {
        setTimeout(() => {
            bar.style.height = bar.style.height;
        }, index * 100);
    });

    // Calories progress animation
    const caloriesProgress = document.querySelector('.calories-progress-inner');
    setTimeout(() => {
        caloriesProgress.style.width = '33%';
    }, 500);

    // Cycle day progress animation
    const progressCircle = document.querySelector('.progress-circle');
    setTimeout(() => {
        progressCircle.style.background = 'conic-gradient(#ff6347 20%, #ffa07a 0)';
    }, 500);
});