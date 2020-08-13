if [ "$1" == "1" ]; then
        #echo "mv index.html indriya-index.html"
        mv index.html indriya-index.html
        #echo "mv upgrade-index.html index.html"
        mv upgrade-index.html index.html
else
        #echo "mv index.html upgrade-index.html"
        mv index.html upgrade-index.html
        #echo "mv indriya-index.html index.html"
        mv indriya-index.html index.html
fi
