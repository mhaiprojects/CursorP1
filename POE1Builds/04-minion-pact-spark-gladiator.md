# Minion Pact Spark Gladiator (Endgame)

**Budget:** ~30–80 divines (budget PoB) → ~100–150 div (700M+ PoB)  
**PoB DPS:** ~403M budget / ~762M endgame (total hit DPS, shock + frenzy configured)  
**Playstyle:** Low-life crit Spark, screen-wide clear, strong bossing  
**Difficulty:** Medium

## Why This Build

Minion Pact Spark is one of the highest-DPS-per-divine archetypes in POE 3.28. The `Minion Pact` support transfers a large portion of your minion life to your character, which then scales `Spark of the Nova` through crit, spell echo, and projectile speed. Community PoBs show **400M+ on a budget loadout** and **760M+** with modest upgrades — far above typical 50–100 div melee builds.

This is an endgame pivot, not a league starter. Farm currency with a starter first, then transition.

## PoB Imports

| Loadout | Link | Reported DPS |
|---------|------|--------------|
| Budget | [pobb.in/MWFT7jEMJXsl](https://pobb.in/MWFT7jEMJXsl) | ~403M |
| Endgame | [pobb.in/02p_KtpAOb4M](https://pobb.in/02p_KtpAOb4M) | ~762M |
| Reference | [pobarchives.com/build/4bzdWRTK](https://pobarchives.com/build/4bzdWRTK) | ~762M |

Import into [Path of Building Community](https://pathofbuilding.community). Enable **Shock (15%)**, **Frenzy**, and **Power Charges** in config to match published DPS.

## Core Mechanic

1. **Summon Stone Golem of Hordes** (or spectres) — stack high minion life via tree, gear, and `Minion Life` support.
2. **Minion Pact** — grants you a percentage of minion life as extra life and damage scaling.
3. **Spark of the Nova** — many fast projectiles; each cast can hit the same target multiple times.
4. **Low Life + Mind Over Matter + Pain Attunement** — `Blood Magic` + `MoM` + `Pain Attunement` for 30% more spell damage on low life.

## Core Skills

| Slot | Gem | Notes |
|------|-----|-------|
| Body 6L | Spark of the Nova → Minion Pact → Increased Critical Strikes → Greater Spell Echo → Faster Projectiles → Pierce | Pinpoint as 7th link if available |
| Gloves | Raise Spectre → Minion Life → Empower → Eclipse | Spectres for frenzy / utility |
| Helmet | Automation → Cooldown Recovery → Convocation → More Duration | Convocation for minion recovery |
| Boots | Zealotry → Arctic Armour → Flesh and Stone → Tempest Shield → Power Charge On Critical | |
| Weapon | Pride → Eternal Blessing | Reservation efficiency via life |
| Movement | Shield Charge → Faster Attacks; Frostblink → Power Charge On Critical | |

## Ascendancy (Gladiator)

1. Painforged  
2. Gratuitous Violence (or Outmatch and Outlast for defence)  
3. Relevant (or Jagged Technique)  
4. Unstoppable Hero / Ascendancy notable for block or damage as needed

Alternative: Elementalist for simpler reservation (`Primal Aegis`), ~371M in community PoBs — Gladiator pushes higher peak DPS.

## Key Passive Tree

- **Keystones:** Blood Magic, Mind Over Matter, Pain Attunement, Bitter Heresy
- **Masteries:** Life (low-life thresholds), Projectile Speed, Reservation Efficiency, Block
- **Tattoos:** Ramako Sniper (projectile speed), Hinekora Warmonger (minion life)
- **Runegraft:** Treachery (reservation) on budget setups

## Budget Gear Priorities (~30–80 div)

| Slot | Budget Target | Why |
|------|---------------|-----|
| Weapon | +1–2 lightning spell gems sceptre or wand | Cheap to craft or buy |
| Body | Rare life-based armour, 6-link | Spark main link |
| Rings/Amulet | Life, resists, cast speed, crit multi | Craft or buy |
| Belt | Stygian or leather belt with life + resists | Abyss jewels for minion life |
| Jewels | Minion life, projectile speed, spell damage | Best DPS per divine here |
| Flasks | Instant life, Quicksilver, Granite/Jade, Diamond | Gladiator block layers |

**Skip early:** Mageblood, expensive Voices, mirror-tier clusters. Budget PoB proves 400M+ without them.

## Upgrade Path (80 → 150 div, 500M–760M+)

1. Better abyss jewels (minion life + attack/cast speed)  
2. Cluster jewels (Minion Life, Critical, Projectile)  
3. +2 gem spark weapon or high crit base  
4. Forbidden Flame/Flesh (optional ascendancy bonus)  
5. Awakened supports (Greater Spell Echo, etc.)

## Defences

- Low-life (~3k–4.8k life) + large mana pool for MoM  
- Block chance from shield + tree (Gladiator)  
- Arctic Armour + Flesh and Stone while mapping  
- Convocation + minion meat shield

## PoB Calculation Notes

- DPS is **total hit DPS** (not poison DOT cap).  
- Spark DPS assumes multiple hits per cast from projectile speed + duration.  
- Verify minion life in PoB matches in-game (spectre/golem setup).  
- See `docs/path-of-building.md` for where Spark and Minion Pact logic lives in the cloned PoB repo (`src/Data/Skills/`, `src/Modules/CalcOffence.lua`).

## Content Viability

- Uber pinnacle bosses (with 400M+ budget loadout)  
- T17 / juiced maps  
- Sanctum (community runs at 762M PoB)  
- Simulacrum / wave 15+
