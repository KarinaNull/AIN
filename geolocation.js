function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
        console.log('Geolocation is not supported by this browser.')
    }
}

function showPosition(position) {
    fetch('http://api.openweathermap.org/geo/1.0/reverse?'
        + 'lat=' + position.coords.latitude
        + '&lon=' + position.coords.longitude
        + '&appid=685a5f6c0ec7432361a364184373d9cd')
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Network response was not ok');
            }
        })
        .then(data => {
            const city = data[0].name
            confirm('Is your city ' + city + '?')
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));
}

function showError(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            console.log('User denied the request for Geolocation.')
            break;
        case error.POSITION_UNAVAILABLE:
            console.log('Location information is unavailable.')
            break;
        case error.TIMEOUT:
            console.log('The request to get user location timed out.')
            break;
        case error.UNKNOWN_ERROR:
            console.log('An unknown error occurred.')
            break;
    }
}

getLocation();