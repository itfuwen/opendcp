/*
 *  Copyright 2009-2016 Weibo, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
package api

import (
	//"fmt"
	"strconv"

	h "weibo.com/opendcp/orion/helper"
	//. "weibo.com/opendcp/orion/models"
	//s "weibo.com/opendcp/orion/service"
	//u "weibo.com/opendcp/orion/utils"
)

const ()

type ExecApi struct {
	baseAPI
}

func (e *ExecApi) URLMapping() {
	e.Mapping("Expand", e.ExpandPool)
	e.Mapping("Shrink", e.ShrinkPool)
	e.Mapping("Deploy", e.DeployPool)
}

func (c *ExecApi) ExpandPool() {

	opUser := c.Ctx.Input.Header("Authorization")
	id := c.Ctx.Input.Param(":id")
	idInt, _ := strconv.Atoi(id)


	req := struct {
		Num int `json:"num"`
	}{}

	err := c.Body2Json(&req)
	if err != nil {
		c.ReturnFailed(err.Error(), 400)
		return
	}

	num := req.Num
	if num < 1 || num > 100 {
		c.ReturnFailed("Bad num: "+strconv.Itoa(num), 400)
		return
	}

	err = h.Expand(idInt, num, opUser)
	if err != nil {
		c.ReturnFailed(err.Error(), 400)
		return
	}

	c.ReturnSuccess(nil)
}

func (c *ExecApi) ShrinkPool() {

	opUser := c.Ctx.Input.Header("Authorization")
	id := c.Ctx.Input.Param(":id")
	poolId, _ := strconv.Atoi(id)

	req := struct {
		Nodes []string `json:"nodes"`
	}{}

	err := c.Body2Json(&req)
	if err != nil {
		c.ReturnFailed(err.Error(), 400)
		return
	}

	nodes := req.Nodes
	err = h.Shrink(poolId, nodes, opUser)
	if err != nil {
		c.ReturnFailed(err.Error(), 400)
		return
	}

	c.ReturnSuccess(nil)
}

func (c *ExecApi) DeployPool() {

	opUser := c.Ctx.Input.Header("Authorization")
	id := c.Ctx.Input.Param(":id")
	poolId, _ := strconv.Atoi(id)

	req := struct {
		MaxNum int    `json:"max_num"`
		Tag    string `json:"tag"`
	}{}

	err := c.Body2Json(&req)
	if err != nil {
		c.ReturnFailed(err.Error(), 400)
		return
	}

	err = h.Deploy(poolId, req.Tag, req.MaxNum, opUser)
	//err = h.Deploy(poolId, req.MaxNum)
	if err != nil {
		c.ReturnFailed(err.Error(), 400)
		return
	}

	c.ReturnSuccess(nil)
}
