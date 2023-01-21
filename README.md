# Block Learning Coach - plugin for Moodle #

Plugin allows to display Learning Coach's profiles to learners and teachers, and statistics by groups of learners.

A Learning Coach profile is defined by 4 dimensions (cognitive, psychosocial, etc.), themselves composed of several constructs. The statistics are obtained thanks to the scores of the learners for each construct.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/blocks/learningcoach

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Documentation ##

More information can be found on https://traindy.io/.

## License ##

Developped by Traindy / 3E-Innovation - 2023

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

## Moodle version supported ##

1.0 (2022011800) for Moodle 3.9, 3.10, 3.11, 4.0, 4.1

Release date January 2023

----------------------------------------------------------------------