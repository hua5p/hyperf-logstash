# Packagist 发布指南

## 概述

本指南将帮助您将 `hua5p/hyperf-logstash` 包发布到 Packagist，使其可以通过 Composer 安装。

## 步骤

### 1. 注册 Packagist 账户

1. 访问 [Packagist.org](https://packagist.org/)
2. 点击 "Register" 注册账户
3. 验证邮箱地址

### 2. 提交包到 Packagist

#### 方法一：通过 Web 界面

1. 登录 Packagist
2. 点击 "Submit Package"
3. 输入 GitHub 仓库 URL：`https://github.com/hua5p/hyperf-logstash`
4. 点击 "Check" 验证包
5. 点击 "Submit" 提交

#### 方法二：通过 API（推荐）

1. 在 Packagist 账户设置中生成 API Token
2. 配置 GitHub Actions 自动发布

### 3. 手动发布到 Packagist

#### 方法一：通过 Web 界面

1. 登录 Packagist
2. 找到您的包：`hua5p/hyperf-logstash`
3. 点击 "Update" 按钮手动更新

#### 方法二：通过 API

如果您有 Packagist API Token，可以使用以下命令：

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"repository":{"url":"https://github.com/hua5p/hyperf-logstash"}}' \
  "https://packagist.org/api/update-package?username=YOUR_USERNAME&apiToken=YOUR_TOKEN"
```

### 4. 版本发布流程

#### 手动发布

```bash
# 1. 更新版本号
./scripts/publish.sh 1.0.0

# 2. 检查 GitHub Release 是否创建成功
# 3. 验证 Packagist 是否更新
```

#### 手动发布

1. 推送标签到 GitHub：`git push origin v1.0.0`
2. 手动触发 Packagist 更新：
   - 访问 Packagist 包页面
   - 点击 "Update" 按钮
   - 或使用 API 命令更新

### 5. 验证发布

#### 测试安装

```bash
# 创建测试项目
mkdir test-install
cd test-install

# 初始化 Composer
composer init --name=test/project --require=hua5p/hyperf-logstash

# 安装包
composer install
```

#### 检查 Packagist 页面

访问：https://packagist.org/packages/hua5p/hyperf-logstash

### 6. 维护包

#### 更新包

1. 修改代码
2. 更新 `CHANGELOG.md`
3. 更新版本号：`./scripts/publish.sh 1.0.1`
4. 推送标签：`git push origin v1.0.1`

#### 包信息更新

- 包描述：修改 `composer.json` 中的 `description`
- 关键词：修改 `composer.json` 中的 `keywords`
- 许可证：修改 `composer.json` 中的 `license`
- 作者：修改 `composer.json` 中的 `authors`

### 7. 故障排除

#### 常见问题

1. **包未出现在 Packagist**
   - 检查 GitHub 仓库是否为公开
   - 确认 `composer.json` 格式正确
   - 验证 Packagist API Token 权限

2. **版本更新失败**
   - 检查版本号格式（语义化版本）
   - 确认标签已推送到 GitHub
   - 验证 Packagist API 调用

3. **依赖问题**
   - 检查 `composer.json` 中的依赖版本
   - 确认所有依赖都可在 Packagist 找到

#### 联系支持

- Packagist 问题：https://github.com/composer/packagist
- Packagist 问题：https://github.com/composer/packagist

## 最佳实践

1. **版本管理**
   - 使用语义化版本控制
   - 及时更新 `CHANGELOG.md`
   - 为每个版本创建 GitHub Release

2. **文档维护**
   - 保持 README.md 最新
   - 提供详细的使用示例
   - 及时更新安装指南

3. **测试**
   - 每次发布前运行完整测试
   - 在多个 PHP 版本上测试
   - 验证安装和基本功能

4. **监控**
   - 关注 Packagist 下载统计
   - 监控 GitHub Issues
   - 及时响应用户反馈 