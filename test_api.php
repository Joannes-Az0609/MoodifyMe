<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
</head>
<body>
    <h2>Testing Meals API</h2>
    <div id="result"></div>
    
    <script>
        // Test the API directly
        const apiUrl = 'http://localhost/MoodifyMe/api/meals.php?action=get_by_mood&source=sad&target=happy&limit=5';
        console.log('Testing API URL:', apiUrl);
        
        fetch(apiUrl)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text(); // Get as text first to see raw response
            })
            .then(text => {
                console.log('Raw response:', text);
                document.getElementById('result').innerHTML = '<pre>' + text + '</pre>';
                
                // Try to parse as JSON
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed JSON:', data);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('result').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            });
    </script>
</body>
</html>
