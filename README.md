# Prosebot

Prosebot is a template-based natural language generation application. Throughout the last years, [ZOS](http://www.zos.pt) has been developing Prosebot, alongside their collaboration with [FEUP](https://sigarra.up.pt/feup/pt/web_page.inicial), as part of the [zerozero.pt](https://www.zerozero.pt) project.

It started as a generator of matches’ summaries from sports data extracted from the [zerozero.pt](https://www.zerozero.pt) database. The system is already being used by [zerozero.pt](https://www.zerozero.pt)’s empowered users to automatically generate summaries for football matches that range from amateur to professional leagues, from senior footballers to the young football academies.

As a result of a dissertation project led by Nuno Cardoso ([LinkedIn](https://www.linkedin.com/in/nmtc01/), [GitHub](https://github.com/nmtc01)) at FEUP, in collaboration with [zerozero.pt](https://www.zerozero.pt), Prosebot is now an open-source system that the community can explore. The system achieved yet a context generalization, making it possible for users to introduce new domains and templates. The current version available has a fully operational football context for generating summaries of matches and a dummy context example for weather reporting.

# Contexts Available

- Football (full)
- Weather (dummy example)

# Supported Languages

Currently, Prosebot has full support for the following languages:
- Brazilian Portuguese
- English
- European Portuguese
- Italian
- Spanish

However, new languages can be added by the community. Please read the following [tutorial](https://github.com/zerozeropt/prosebot/wiki/How-to-add-a-new-language) on how to add new languages to the system.

# The components

The Prosebot system is composed of the following components as depicted [here](https://github.com/zerozeropt/prosebot/wiki/prosebot/Architecture):
- Prosebot (generator and validator): the PHP natural language generator and templates validation algorithm;
- Prosebot Editor: a copy of the template files and API for their management;
- Templates Management Platform: a React JS user-friendly interface for helping manage of the templates.

# Get started

## Requirements

- PHP version 7.4.28.
- Node.js (tested with version 16.15.0).
- npm (tested with version 8.5.5).

### Prosebot

Prosebot was developed under PHP version 7.4.28.
To start the Prosebot application run:
```
cd prosebot
php -S localhost 8080
```

### Prosebot Editor

To start the Prosebot Editor API run:
```
cd prosebot-editor
php -S localhost 8081
```

### Templates Management Platform

The web-app was developed under the CoreUI Free React Admin Template version 4.3.0.
To start the Templates Management Platform run:
```
cd web-app
npm install
npm start
```

### Demo

[Video file](https://github.com/zerozeropt/prosebot/blob/main/Prosebot.mp4)

# Documentation

For more details regarding architecture, descriptions, and tutorials, please read the [Prosebot Documentation](https://github.com/zerozeropt/prosebot/wiki/Home).

# License

Prosebot is licensed under GNU General Public License version 3.0.

[OpenWeather(TM)](https://openweathermap.org) is the weather data provider of the samples used in the dummy weather example. This [sample data](/prosebot/contexts/weather/samples/) is made available under the Open Database License: http://opendatacommons.org/licenses/odbl/1.0/. Any rights in individual contents of the database are licensed under the Database Contents License: http://opendatacommons.org/licenses/dbcl/1.0/
