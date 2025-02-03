# Pour linux
# fichier launch.sh
cd chat_bot
rasa train
rasa run actions &  # démarre en arrière-plan
rasa shell --endpoints endpoints.yml

# Dans terminal : chmod +x launch.sh && ./launch.sh
