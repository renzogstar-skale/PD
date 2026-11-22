const { getDefaultConfig } = require('expo/metro-config');
const path = require('path');

const config = getDefaultConfig(__dirname);

// Diccionario de indicadores compartido con la web (fuente única en public/data)
config.watchFolders = [...(config.watchFolders || []), path.resolve(__dirname, '../public/data')];

module.exports = config;
