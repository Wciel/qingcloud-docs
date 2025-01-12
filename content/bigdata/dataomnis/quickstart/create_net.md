---
title: "创建网络"
description: 本小节主要介绍如何创建网络。 
keywords: 大数据工作台,创建网络
weight: 14
collapsible: false
draft: false
---

本小节为您介绍如何创建网络。

## 前提条件

在创建网络前，建议先创建好依赖的 VPC 和私有网络。详细操作请参见[创建 VPC 网络](/network/vpc/manual/vpcnet/10_create_vpc/)。

## 使用限制

- 仅支持使用工作空间所在区域的 VPC。
- 不支持使用免费型 VPC（由于免费型 VPC 不具备公网访问能力，暂时不支持在免费型 VPC 中创建计算集群）。

## 操作步骤

1. 登录管理控制台。
2. 选择**产品与服务** > **大数据服务** > **大数据工作台**，进入大数据工作台概览页面。
3. 在左侧导航选择**工作空间**，进入工作空间页面。
4. 在目标工作空间选择**数据开发** > **网络配置**，进入网络配置页面。
5. 点击**创建网络**，进入创建网络页面。
   
   <img src="/bigdata/dataomnis/_images/create_net.png" alt="创建网络" style="zoom:50%;" />

6. 配置相关参数。

   | <span style="display:inline-block;width:140px">参数</span>  | <span style="display:inline-block;width:520px">参数说明</span>  |
   | :------------- | ------------------------------------------------------------ |
   | 网络名称 |  创建的网络名称，您可以自定义。              |
   | VPC 网络    |  选择 VPC 网络。<br>- 默认适配同区域已有的 VPC 网络。可在下拉框选择已有 VPC 网络。<br>- 若无可选 VPC 网络，可点击**新建 VPC 网络**，创建依赖网络资源。  |
   | 私有网络    |  选择私有网络。<br>- 默认适配同区域已有的私有网络。可在下拉框选择已有私有网络。<br>- 若无可选私有网络，可点击**新建私有网络**，创建依赖网络资源。   |

7. 点击**创建**，开始创建网络。