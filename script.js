
const searchBtn = document.querySelector('.search-btn');
const searchBox = document.querySelector('.search-box');

const movieInfoSection = document.querySelector('.movie-info');
const notFoundSection = document.querySelector('.not-found');
const movieTitle = document.querySelector('.movie-title');
const movieYear = document.querySelector('.movie-year');
const movieGenre = document.querySelector('.movie-genre');
const movieDirector = document.querySelector('.movie-director');
const movieActors = document.querySelector('.movie-actors');
const moviePlot = document.querySelector('.movie-plot');
const moviePoster = document.querySelector('.movie-poster');

// Event listener for search button
searchBtn.addEventListener('click', () => {
    const searchValue = searchBox.value.trim();
    if (searchValue === '') {
        handleEmptySearch();
    } else {
        getMovieData(searchValue);
        searchBox.value = '';  // Clear input
        searchBox.blur();  // Remove focus from the input field
    }
});

// Event listener for Enter key press in search box
searchBox.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        const searchValue = searchBox.value.trim();
        if (searchValue === '') {
            handleEmptySearch();
        } else {
            getMovieData(searchValue);
            searchBox.value = '';  // Clear input
            searchBox.blur();  // Remove focus
        }
    }
});

// Empty search handler
function handleEmptySearch() {
    alert('Please enter a movie name to search!');
}

// Function to fetch movie data from the PHP backend
async function getMovieData(movieName) {
    const url = `http://localhost/movie-app/index.php?t=${movieName}`;
    try {
        // Fetch movie data from the backend
        const response = await fetch(url);
        const data = await response.json();

        console.log('Data received from backend:', data);  // Log the response for debugging
        
        if (data && data.length > 0) {
            updateMovieInfo(data[0]);  // Display the first movie result
        } else {
            showNotFound();
        }
    } catch (error) {
        console.error('Error fetching data:', error);
        showNotFound();
    }
}

// Update the UI with movie details
function updateMovieInfo(data) {
    console.log('Updating movie info:', data);  // Log the movie data to check

    movieTitle.textContent = data.Movie_Name || 'Title not available';
    movieYear.textContent = `Year: ${data.R_Year || 'N/A'}`;
    movieGenre.textContent = `Genre: ${data.Genre || 'N/A'}`;
    movieDirector.textContent = `Director: ${data.Director || 'N/A'}`; 
    movieActors.textContent = `Actors: ${data.Actors || 'N/A'}`; 
    moviePlot.textContent = `Plot: ${data.Plot || 'N/A'}`; 
    moviePoster.src = data.Poster !== 'N/A' ? data.Poster : 'https://via.placeholder.com/200';

    movieInfoSection.style.display = 'block';
    notFoundSection.style.display = 'none';
}

// Show the "Movie Not Found" section
function showNotFound() {
    notFoundSection.style.display = 'block';
    movieInfoSection.style.display = 'none';
}
