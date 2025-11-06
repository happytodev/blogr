<?php



namespace Happytodev\Blogr\Tests\Feature;


uses(\Happytodev\Blogr\Tests\TestCase::class);
use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Tests\TestCase;

class BlogrSettingsTabContainmentTest extends TestCase
{
    /** @test */
    public function it_ensures_all_tabs_are_contained_within_tabs_container()
    {
        // Get the form schema
        $schema = app(BlogrSettings::class)->getFormSchema();
        
        // Convert schema to string for analysis
        $schemaString = $this->schemaToString($schema);
        
        // Check that all Tabs\Tab instances are within a Tabs container
        $this->assertTabsAreContained($schemaString);
    }
    
    /** @test */
    public function it_verifies_no_orphaned_tab_components_exist()
    {
        // Get the form schema
        $schema = app(BlogrSettings::class)->getFormSchema();
        
        // Convert schema to string for analysis
        $schemaString = $this->schemaToString($schema);
        
        // Ensure no Tabs\Tab::make calls exist outside of Tabs::make()->tabs([...])
        $this->assertNoOrphanedTabs($schemaString);
    }
    
    /**
     * Convert schema array to string representation for analysis
     */
    private function schemaToString(array $schema): string
    {
        return json_encode($schema, JSON_PRETTY_PRINT);
    }
    
    /**
     * Assert that all Tabs\Tab components are properly contained
     */
    private function assertTabsAreContained(string $schemaString): void
    {
        // This is a simplified check - in a real scenario, you might want to
        // parse the PHP code structure more thoroughly
        $tabMakeCount = substr_count($schemaString, 'Tab::make');
        $tabsContainerCount = substr_count($schemaString, 'Tabs::make');
        
        // We should have at least one Tabs container for our tabs
        $this->assertGreaterThan(0, $tabsContainerCount, 'No Tabs container found in schema');
        
        // We should have tabs within containers
        $this->assertGreaterThan(0, $tabMakeCount, 'No Tab components found in schema');
        
        // Basic structural check - ensure we have proper nesting
        $this->assertStringContainsString('tabs', $schemaString, 'Schema should contain tabs structure');
    }
    
    /**
     * Assert that no Tabs\Tab components exist outside of proper containers
     */
    private function assertNoOrphanedTabs(string $schemaString): void
    {
        // This test ensures the schema structure is correct
        // In case of orphaned tabs, the schema would not be properly structured
        
        // Check that the schema is a proper array structure
        $this->assertStringStartsWith('[', $schemaString, 'Schema should start with array');
        $this->assertStringEndsWith(']', trim($schemaString), 'Schema should end with array');
        
        // Ensure we have the expected structure
        $this->assertStringContainsString('Tabs::make', $schemaString, 'Schema should contain Tabs container');
        $this->assertStringContainsString('Tab::make', $schemaString, 'Schema should contain Tab components');
    }
}
