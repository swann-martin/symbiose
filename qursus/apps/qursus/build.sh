#!/bin/bash
npm run build && npm run webpack 
zip web.app ./build/* ./environment.json ./index.html ./style.css  ./background.jpeg ./qursus.bundle.js ./equal.bundle.js