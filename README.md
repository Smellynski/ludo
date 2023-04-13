# description

A simple ludo game. </br>
Playable with up to 4 people.

# installation

<!-- Copy to clipboard script -->
<script>
    function copyToClipboard(text) {
        const input = document.createElement('textarea');
        input.innerHTML = text;
        document.body.appendChild(input);
        input.select();
        const result = document.execCommand('copy');
        document.body.removeChild(input);
        return result;
    }
</script>

<!-- Installation instructions with copy button -->
-> clone this repository <button onclick="copyToClipboard('git clone https://github.com/Smellynski/ludo.git')">Copy</button> ```git clone https://github.com/Smellynski/ludo.git``` </br>
-> install [Docker](https://www.docker.com/) <button onclick="copyToClipboard('https://www.docker.com/')">Copy</button> </br>
-> to set it up, use the following commands: </br>
    1.  Go into the directory of the game </br>
    2.  paste the following command into your terminal </br>
        - <button onclick="copyToClipboard('docker-compose build')">Copy</button> ```docker-compose build``` </br>
            - wait until its finished </br>
        - <button onclick="copyToClipboard('docker-compose run')">Copy</button> ```docker-compose run``` </br>
-> create a local mysql database and use the `databaseSetup.sql` to set it up</br>
-> use the`.env.example`file to create your`.env` file
    
