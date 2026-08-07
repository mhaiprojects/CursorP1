# Path of Building — Repository Overview

Path of Building (PoB) is the community-maintained offline build planner for Path of Exile 1. This project vendors a shallow clone at `repos/path-of-building/` from [PathOfBuildingCommunity/PathOfBuilding](https://github.com/PathOfBuildingCommunity/PathOfBuilding).

## Top-Level Layout

| Path | Purpose |
|------|---------|
| `src/` | Application source (Lua) |
| `src/Data/` | Static game data used at runtime |
| `src/Modules/` | Core logic: data loading, mod parsing, calculations |
| `src/Classes/` | UI controls and build/item/tree views |
| `src/Export/` | Tools to extract and convert data from the game GGPK |
| `src/TreeData/` | Passive skill tree definitions per game version |
| `runtime/` | Lua runtime, fonts, and SimpleGraphic host assets |
| `docs/` | Upstream contributor docs (mod syntax, offence calcs, etc.) |
| `spec/` | Automated tests |

## Where Item Data Lives

All item-related static data is under `src/Data/`.

### Base item types

`src/Data/Bases/` — one Lua file per equipment category (axe, bow, helmet, ring, etc.). Each file defines base types, implicit mods, and weapon stats.

### Unique items

`src/Data/Uniques/` — unique item definitions by slot. `Special/` holds edge cases. Newly announced uniques land in `src/Data/New.lua` until mod ranges are confirmed.

### Item modifiers

| File | Content |
|------|---------|
| `ModExplicit.lua` | Prefix/suffix mods on rare items |
| `ModItemExclusive.lua` | Item-specific explicit mods |
| `ModVeiled.lua`, `ModDelve.lua`, `ModScourge.lua`, etc. | League-specific mod pools |
| `ModJewel.lua`, `ModJewelCluster.lua`, `ModJewelAbyss.lua` | Jewel mods |
| `ModFlask.lua`, `ModTincture.lua`, `ModGraft.lua` | Flask and special slot mods |
| `ModCache.lua` | Pre-parsed mod lookup table (generated) |
| `Rares.lua` | Rare item templates |
| `Essence.lua` | Essence crafting outcomes |
| `Enchantment*.lua` | Crafted bench enchantments by slot |

### Skills, gems, and support data

| Path | Content |
|------|---------|
| `src/Data/Skills/` | Active and support skill definitions |
| `src/Data/Gems.lua` | Skill gem metadata |
| `src/Data/SkillStatMap.lua` | Maps in-game stat text to internal stat names |
| `src/Data/StatDescriptions/` | Stat description templates from game files |

### Other game data

`ClusterJewels.lua`, `Minions.lua`, `Spectres.lua`, `Pantheons.lua`, `Bosses.lua`, `Costs.lua`, `TimelessJewelData/`, and `Global.lua` (colour codes, mod flags, skill type enums).

### Data loading entry point

`src/Modules/Data.lua` loads all of the above: bases, uniques, skills, mods, enchantments, essences, pantheons, gems, and minions. It also holds shared lookups (jewel radius, weapon info, monster XP).

### Export pipeline (source of truth)

Game data is originally extracted from the Path of Exile GGPK via `src/Export/`:

- `src/Export/Bases/` — raw base type exports
- `src/Export/Uniques/` — raw unique exports
- `src/Export/Skills/` — skill gem text dumps
- `src/Export/ggpk/` — GGPK extraction helpers

Exported data is converted into the Lua files in `src/Data/`.

## Where Calculation Logic Lives

The calculation engine is modular under `src/Modules/`.

### Orchestration

| Module | Role |
|--------|------|
| `Calcs.lua` | Entry point; loads all calc modules, runs full DPS/stat passes |
| `CalcSetup.lua` | Builds the calculation environment: player/enemy mod DBs, passive tree, items, flasks, jewels, skill groups |
| `CalcPerform.lua` | Main perform loop; coordinates offence and defence passes |
| `CalcTools.lua` | Mod value math (increased/more), gem validation, stat tables |

### Offence and defence

| Module | Role |
|--------|------|
| `CalcOffence.lua` | Hit damage, DPS, ailments, debuffs; per-hand attack passes |
| `CalcDefence.lua` | Life, ES, armour, evasion, resistances, mitigation |
| `CalcActiveSkill.lua` | Per-skill effect and support gem application |
| `CalcTriggers.lua` | Triggered skill and cascade logic |
| `CalcMirages.lua` | Mirage Archer, phantasm, and similar effects |
| `CalcBreakdown.lua` | Detailed stat breakdown for the Calcs tab UI |
| `CalcSections.lua` | Groups breakdown output into UI sections |
| `CalcFormat.lua` | Number formatting for display |

### Mod parsing (feeds calculations)

| Module | Role |
|--------|------|
| `ModParser.lua` | Parses mod text from items/skills into structured mods; builds `ModCache` |
| `ModTools.lua` | Mod creation helpers; loads `ModCache` at startup |
| `ItemTools.lua` | Item text parsing, mod range calculation, influence handling |
| `StatDescriber.lua` | Converts internal stats to human-readable descriptions |

### Core data structures

`src/Classes/ModDB.lua`, `ModList.lua`, and `ModStore.lua` hold modifier databases. `CalcSetup` populates these from tree nodes, gear, buffs, and config; `CalcPerform` reads them to produce `env.player.output`.

### Calculation flow (simplified)

```
Build XML
  → CalcSetup.initEnv()     # tree + items + gems → mod DBs
  → CalcPerform.perform()   # offence + defence passes
  → Calcs.calcFullDPS()     # aggregate skill DPS
  → BuildDisplayStats       # sidebar + Calcs tab
```

For offence internals, see upstream `docs/calcOffence.md`. For mod syntax, see `docs/modSyntax.md`.

## Related Docs in This Repo

- `POE1Builds/` — budget build recommendations you can import into PoB
- `repos/README.md` — other cloned POE open-source tools
