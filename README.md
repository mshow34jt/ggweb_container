# ggweb_container

docker build -t ggweb .

docker run -d --network=host --name ggweb -v /etc/localtime:/etc/localtime  ggweb
