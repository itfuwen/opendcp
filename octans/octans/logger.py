#!/usr/bin/env python
#
#  Copyright 2009-2016 Weibo, Inc.
#
#    Licensed under the Apache License, Version 2.0 (the "License");
#    you may not use this file except in compliance with the License.
#    You may obtain a copy of the License at
#
#        http://www.apache.org/licenses/LICENSE-2.0
#
#    Unless required by applicable law or agreed to in writing, software
#    distributed under the License is distributed on an "AS IS" BASIS,
#    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#    See the License for the specific language governing permissions and
#    limitations under the License.
#
# -*- coding: utf-8 -*-

# Author: WhiteBlue
# Time  : 2016/07/20

import logging
from logging.handlers import RotatingFileHandler




_formatter = logging.Formatter('[%(name)s] %(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s',
                               datefmt='%a, %d %b %Y %H:%M:%S', )

_stream_handler = logging.StreamHandler()
_file_handler = RotatingFileHandler(filename="log/channel.log", maxBytes=1024 * 1024, backupCount=5)

_stream_handler.setFormatter(_formatter)
_file_handler.setFormatter(_formatter)


class LogManager:
    Level = logging.DEBUG

    def __init__(self):
        pass

    @staticmethod
    def get_logger(name):
        logger = logging.getLogger(name)

        logger.setLevel(LogManager.Level)

        logger.addHandler(_stream_handler)
        logger.addHandler(_file_handler)

        return logger
