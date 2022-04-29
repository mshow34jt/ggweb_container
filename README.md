# ggweb_container

docker build -t ggweb .

docker run --network=host --name ggweb -v /etc/localtime:/etc/localtime  ggweb
