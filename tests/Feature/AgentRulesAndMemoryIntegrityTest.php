<?php

namespace Tests\Feature;

use Tests\TestCase;

class AgentRulesAndMemoryIntegrityTest extends TestCase
{
    public function test_root_rule_files_exist_and_contain_core_directives(): void
    {
        $this->assertFileExists(base_path('AGENTS.md'));
        $this->assertFileExists(base_path('GEMINI.md'));

        $agentsContent = file_get_contents(base_path('AGENTS.md'));
        $geminiContent = file_get_contents(base_path('GEMINI.md'));

        $this->assertStringContainsString('# AGENTS.md — Global Workspace Directives', $agentsContent);
        $this->assertStringContainsString('Windows PowerShell', $agentsContent);
        $this->assertStringContainsString('RFC 2119', $agentsContent);

        $this->assertStringContainsString('# GEMINI.md — Gemini Engine System Directives', $geminiContent);
        $this->assertStringContainsString('.agents/session_memory.md', $geminiContent);
    }

    public function test_modular_rule_files_exist_in_agents_directory(): void
    {
        $rulesDir = base_path('.agents/rules');
        $this->assertDirectoryExists($rulesDir);

        $expectedRules = [
            'environment-windows-powershell.md',
            'laravel-backend-architecture.md',
            'frontend-ui-a11y-performance.md',
            'testing-quality-standards.md',
        ];

        foreach ($expectedRules as $ruleFile) {
            $path = $rulesDir . DIRECTORY_SEPARATOR . $ruleFile;
            $this->assertFileExists($path, "Rule file {$ruleFile} must exist");
            $content = file_get_contents($path);
            $this->assertNotEmpty($content);
            $this->assertStringContainsString('MUST', $content);
        }
    }

    public function test_session_memory_and_protocol_exist_with_required_sections(): void
    {
        $memoryPath = base_path('.agents/session_memory.md');
        $protocolPath = base_path('.agents/session_protocol.md');

        $this->assertFileExists($memoryPath);
        $this->assertFileExists($protocolPath);

        $memoryContent = file_get_contents($memoryPath);
        $this->assertStringContainsString('## 1. Project Architecture Snapshot', $memoryContent);
        $this->assertStringContainsString('## 2. Core Invariants & Security Rules', $memoryContent);
        $this->assertStringContainsString('## 3. Lessons Learned & Known Gotchas', $memoryContent);
        $this->assertStringContainsString('## 4. Session History & Evolution Ledger', $memoryContent);

        $protocolContent = file_get_contents($protocolPath);
        $this->assertStringContainsString('### Step 1: Session Boot (Pre-flight)', $protocolContent);
        $this->assertStringContainsString('### Step 2: In-flight Execution', $protocolContent);
        $this->assertStringContainsString('### Step 3: Session Handoff (Post-flight)', $protocolContent);
    }
}
